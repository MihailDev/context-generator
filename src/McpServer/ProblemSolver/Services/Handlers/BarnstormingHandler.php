<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Brainstorming;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\BrainstormingRepositoryInterface;

final readonly class BarnstormingHandler implements StepHandlerInterface
{
    public function __construct(
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

    public function getContinueInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getContinueInstruction() method.
    }

    public function getFinishInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getFinishInstruction() method.
    }
}
