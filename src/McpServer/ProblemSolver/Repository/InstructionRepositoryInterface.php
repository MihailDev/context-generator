<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemInstruction;

interface InstructionRepositoryInterface
{
    public function getInstructionContent(ProblemInstruction $instruction): string;
}
