<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;

/**
 * Interface for step handlers in the Problem Solver.
 * Each problem step has its own handler with step-specific logic.
 */
interface StepHandlerInterface
{
    public function instructionsOnWrongAction(string $message): ProblemActionInstructions;

    public function getContinueInstruction(Problem $problem): ProblemActionInstructions;
}
