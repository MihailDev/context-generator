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

    public static function fromArray(array $data): Brainstorming
    {
        return new self(
            $data['problem_id'],
            $data['participants'],
            $data['context'],
        );
    }

    public function toArray(): array
    {
        return [
            'problem_id' => $this->problem_id,
            'participants' => $this->participants,
            'context' => $this->context,
        ];
    }
}
