<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemDocumentRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\StepHandlerInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\AnalyzeHandler;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\BarnstormingHandler;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\PlanHandler;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\ChangesHandler;
use Psr\Container\ContainerInterface;

/**
 * Service for managing problems.
 */
final readonly class ProblemService
{
    public function __construct(
        private ProblemRepositoryInterface $problemRepository,
        private ProblemDocumentRepositoryInterface $problemDocumentRepository,
        private AnalyzeHandler $analyzeHandler,
        private BarnstormingHandler $barnstormingHandler,
        private PlanHandler $planHandler,
        private ChangesHandler $changesHandler,
        private ContainerInterface $container,
    ) {}

    /**
     * Create a new problem with the given description.
     *
     * @param string $problemDescription Original problem description
     * @param string|null $problemId Custom problem ID (generated if null)
     * @return Problem The created problem
     * @throws \InvalidArgumentException If problem with given ID already exists
     */
    public function createProblem(
        string  $problemDescription,
        ?string $problemId = null,
        ?string $projectName = null,
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

        if ($projectName !== null) {
            $problem->setDefaultProject($projectName);
        }

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
     * Update problem details to start brainstorming phase.
     *
     * @param Problem $problem The problem to update
     * @param string $problemType Type of the problem
     * @param string $defaultProject Default project
     * @param string $brainstormingDraft Draft guide for brainstorming
     * @param array<string, mixed> $context Problem context
     * @return Problem The updated problem
     */
    public function startBrainstorming(
        Problem $problem,
        string  $problemType,
        string  $defaultProject,
        string  $brainstormingDraft,
        array   $context,
    ): bool {

        $problem->setCurrentStep(ProblemStep::BRAINSTORMING);

        return $this->saveAnalyze(
            $problem,
            $problemType,
            $defaultProject,
            $brainstormingDraft,
            $context,
        );

    }

    /**
     * Start the plan step for a problem.
     *
     * @param Problem $problem The problem to update
     * @return bool True if successful
     */
    public function startPlanStep(Problem $problem): bool
    {
        $problem->setCurrentStep(ProblemStep::PLAN);
        return $this->save($problem);
    }

    /**
     * Start the solve step for a problem.
     *
     * @param Problem $problem The problem to update
     * @return bool True if successful
     */
    public function startSolveStep(Problem $problem): bool
    {
        $problem->setCurrentStep(ProblemStep::CHANGES);
        return $this->save($problem);
    }

    /**
     * Mark a problem as completed.
     *
     * @param Problem $problem The problem to complete
     * @return bool True if successful
     */
    public function completeProblem(Problem $problem): bool
    {
        $problem->setCurrentStep(ProblemStep::COMPLETED);
        return $this->save($problem);
    }

    /**
     * Check if the problem is at the expected step.
     *
     * @param Problem $problem The problem to check
     * @param ProblemStep $expectedStep The expected step
     * @throws \InvalidArgumentException If problem is not at the expected step
     */
    public function checkStep(
        Problem     $problem,
        ProblemStep $expectedStep,
    ): void {
        if ($problem->getCurrentStep() !== $expectedStep) {
            throw new \InvalidArgumentException(
                \sprintf(
                    "Problem is at step '%s', but expected step is '%s'",
                    $problem->getCurrentStep()->value,
                    $expectedStep->value,
                ),
            );
        }
    }

    /**
     * Handle continue action for a problem.
     *
     * @param Problem $problem The problem to continue
     */
    public function onContinue(Problem $problem): void
    {
        // Clear any return reason when continuing
        $problem->setReturnReason(null);

        // Save the problem with updated state
        $this->problemRepository->save($problem);
    }

    /**
     * Restore a problem to a specific step.
     *
     * @param Problem $problem The problem to restore
     * @param ProblemStep $step The step to restore to
     * @param string $returnReason The reason for restoring to this step
     * @throws \InvalidArgumentException If trying to restore to a later step
     */
    public function restoreToStep(
        Problem     $problem,
        ProblemStep $step,
        string      $returnReason,
    ): void {
        // Set the return reason
        $problem->setReturnReason($returnReason);

        // Update the current step
        $problem->setCurrentStep($step);

        // Save the problem
        $this->save(
            $problem,
            false,
        );
    }

    /**
     * Save a problem to the repository.
     *
     * @param Problem $problem The problem to save
     * @return bool True if successful
     */
    public function save(
        Problem $problem,
        bool    $clearReturnReason = true,
    ): bool {
        if ($clearReturnReason) {
            $problem->setReturnReason(null);
        }

        return $this->problemRepository->save($problem);
    }

    /**
     * Update the context for a problem.
     *
     * @param Problem $problem The problem to update
     * @param array $context New context data
     * @return bool True if successful
     */
    public function updateProblemContext(Problem $problem, array $context): bool
    {
        $problem->setContext($context);
        return $this->save($problem);
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

    public function saveAnalyze(
        Problem $problem,
        string  $problemType,
        string  $defaultProject,
        string  $brainstormingDraft,
        array   $context,
    ): bool {
        $problem->setType($problemType)
            ->setDefaultProject($defaultProject)
            ->setContext($context);

        $this->problemDocumentRepository->setBrainstormingDraft($problem, $brainstormingDraft);

        if ($problem->getCurrentStep() === ProblemStep::NEW) {
            $problem->setCurrentStep(ProblemStep::ANALYZE);
        }

        return $this->problemRepository->save($problem);
    }

    public function getHandler(Problem $problem): StepHandlerInterface
    {
        return match ($problem->getCurrentStep()) {
            ProblemStep::NEW, ProblemStep::ANALYZE => $this->analyzeHandler,
            ProblemStep::BRAINSTORMING => $this->barnstormingHandler,
            ProblemStep::PLANNING => $this->planHandler,
            ProblemStep::IMPLEMENTATION => $this->changesHandler,
            default => throw new \InvalidArgumentException(
                "No handler available for step: {$problem->getCurrentStep()->value}",
            ),
        };
    }

    /**
     * Generate a unique problem ID.
     *
     * @return string Generated problem ID
     */
    private function generateProblemId(): string
    {
        return 'local-' . \date('YmdHis');
    }
}
