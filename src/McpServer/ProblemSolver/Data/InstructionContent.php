<?php

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Data;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemInstruction;
use Spiral\Files\FilesInterface;

readonly class InstructionContent
{

    public function __construct(
        private FilesInterface $files,
    ) {}

    public function getInstructionContent(ProblemInstruction $instruction):string
    {
        $path = $this->getInstructionPath($instruction->value);
        return $this->files->read($path);
    }

    private function getInstructionPath(string $name): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'DefaultInstructions' . DIRECTORY_SEPARATOR . $name . '.md';
    }

    public function getFirstAnalyzeInstruction(): string
    {
        return $this->getInstructionContent(ProblemInstruction::FirstAnalyzeInstruction);
    }

    public function getAnalyzeInstruction(): string
    {
        return $this->getInstructionContent(ProblemInstruction::AnalyzeInstruction);
    }
}
