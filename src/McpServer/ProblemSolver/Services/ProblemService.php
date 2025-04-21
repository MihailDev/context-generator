<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\WorkflowStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemRepository;

/**
 * Service for managing problems.
 */
class ProblemService
{
    public function __construct(
        private readonly ProblemRepository $problemRepository,
    ) {}

    /**
     * Create a new problem with the given description.
     *
     * @param string $problemDescription Original problem description
     * @param string|null $problemId Custom problem ID (generated if null)
     * @return Problem The created problem
     */
    public function createProblem(
        string  $problemDescription,
        ?string $problemId = null,
    ): Problem {
        $id = $problemId ?? $this->generateProblemId();

        // Check if a problem with this ID already exists
        if ($this->problemRepository->exists($id)) {
            throw new \InvalidArgumentException("Problem with ID {$id} already exists");
        }

        $problem = new Problem(
            $id,
            $problemDescription,
        );
        $this->problemRepository->save($problem);

        return $problem;
    }

    /**
     * Get a problem by its ID.
     *
     * @param string $problemId The problem ID
     * @return Problem The problem
     * @throws \InvalidArgumentException If problem not found
     */
    public function getProblem(string $problemId): Problem
    {
        $problem = $this->problemRepository->findById($problemId);

        if ($problem === null) {
            throw new \InvalidArgumentException("Problem with ID {$problemId} not found");
        }

        return $problem;
    }

    /**
     * Update problem details for Step 1.
     *
     * @param Problem $problemId Problem ID
     * @param string $problemType Type of the problem
     * @param string $defaultProject Default project
     * @param string $brainstormingDraft Draft guide for brainstorming
     * @param array $context Problem context
     * @return Problem The updated problem
     */
    public function startBrainstorming(
        Problem $problem,
        string  $problemType,
        string  $defaultProject,
        string  $brainstormingDraft,
        array   $context,
    ): Problem {
        $problem->setType($problemType)
            ->setDefaultProject($defaultProject)
            ->setBrainstormingDraft($brainstormingDraft)
            ->setContext($context)
        ->setCurrentStep(WorkflowStep::BRAINSTORMING);

        $this->problemRepository->save($problem);

        return $problem;
    }

    /**
     * Save a problem to the repository.
     *
     * @param Problem $problem The problem to save
     * @return Problem The saved problem
     */
    public function save(Problem $problem): Problem
    {
        $this->problemRepository->save($problem);
        return $problem;
    }

    /**
     * List all available problem IDs.
     *
     * @return array<string> List of problem IDs
     */
    public function listProblemIds(): array
    {
        return $this->problemRepository->listIds();
    }

    /**
     * Check if a problem exists.
     *
     * @param string $problemId Problem ID
     * @return bool True if the problem exists, false otherwise
     */
    public function problemExists(string $problemId): bool
    {
        return $this->problemRepository->exists($problemId);
    }

    public function onContinue(Problem $problem): void {}

    public function restoreToStep(
        Problem      $problem,
        WorkflowStep $step,
        string       $return_reason,
    ): void {}

    /**
     * Generate a unique problem ID.
     *
     * @return string Generated problem ID
     */
    private function generateProblemId(): string
    {
        return 'problem_' . \date('Ymd') . '_' . \substr(
            \md5(
                \uniqid(
                    (string) \mt_rand(),
                    true,
                ),
            ),
            0,
            8,
        );
    }
}
