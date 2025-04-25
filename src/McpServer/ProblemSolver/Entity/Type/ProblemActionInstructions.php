<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type;

use Mcp\Types\CallToolResult;
use Mcp\Types\TextContent;

class ProblemActionInstructions
{
    public function __construct(
        public array $instructions = [],
    ) {}

    public function getCallContents(): array
    {
        return \array_map(static fn($item) => new TextContent($item), $this->instructions);
    }

    public function toCallToolResult(): CallToolResult
    {
        return new CallToolResult(
            $this->getCallContents(),
        );
    }

    public function add(string $instruction): static
    {
        $this->instructions[] = $instruction;
        return $this;
    }
}
