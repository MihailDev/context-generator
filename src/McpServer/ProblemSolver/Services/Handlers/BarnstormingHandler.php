<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Brainstorming;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\BrainstormingRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Butschster\ContextGenerator\McpServer\ProjectService\ProjectServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Handler for the Barnstorming step in problem solving.
 * Handles the brainstorming and task creation phase.
 */
final readonly class BarnstormingHandler implements StepHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ProblemService $problemService,
        private InstructionService $instructionService,
        private ProjectServiceInterface $projectService,
        private BrainstormingRepositoryInterface $brainstormingRepository,
    ) {}

    public function addBarnstorming(Problem $problem): Brainstorming
    {
        $brainstorming = new Brainstorming(
            problem_id: $problem->getId(),
            participants: [],
            context: [],
        );

        $this->brainstormingRepository->save($brainstorming);

        return $brainstorming;
    }

    public function instructionsOnWrongAction(string $message): ProblemActionInstructions
    {
        // TODO: Implement instructionsOnWrongAction() method.
    }

    public function getContinueInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getContinueInstruction() method.
    }

    public function getFinishInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getFinishInstruction() method.
    }
}
