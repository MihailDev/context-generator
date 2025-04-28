<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;

/**
 * Handler for the Changes step in problem solving.
 * Handles actual implementation of changes to solve the problem.
 */
final readonly class ChangesHandler implements StepHandlerInterface
{
    public function getContinueInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getContinueInstruction() method.
    }

    public function getFinishInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getFinishInstruction() method.
    }
}
