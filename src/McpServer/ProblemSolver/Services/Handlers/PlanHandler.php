<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;

final readonly class PlanHandler implements StepHandlerInterface
{
    public function getContinueInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getContinueInstruction() method.
    }

    public function getFinishInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getFinishInstruction() method.
    }

    public function startInstructions(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement startInstructions() method.
    }
}
