<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Entity;

class Brainstorming
{
    public function __construct(
        public string $problem_id,
        public array $participants,
        public array $context,
    ) {}
}
