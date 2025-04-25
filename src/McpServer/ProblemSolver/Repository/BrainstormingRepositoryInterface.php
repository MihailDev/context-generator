<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Brainstorming;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;

interface BrainstormingRepositoryInterface
{
    public function save(Brainstorming $problem): bool;

    public function findById(Problem $problem): ?Brainstorming;

    public function exists(Problem $problem): bool;
}
