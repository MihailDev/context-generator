<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;

interface StepHandlerInterface
{
    public function getContinueInstruction(Problem $problem): ProblemActionInstructions;

    public function getFinishInstruction(Problem $problem): ProblemActionInstructions;

    public function startInstructions(Problem $problem): ProblemActionInstructions;
}
