<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemInstruction;
use Spiral\Files\FilesInterface;

/**
 * Service for loading instruction content from files.
 */
readonly class FileInstructionRepository implements InstructionRepositoryInterface
{
    public function __construct(
        private FilesInterface $files,
    ) {}

    /**
     * Get instruction content by enum type.
     *
     * @param ProblemInstruction $instruction The instruction type
     * @return string The instruction content
     */
    public function getInstructionContent(ProblemInstruction $instruction): string
    {
        $path = $this->getInstructionPath($instruction->value);
        return $this->files->read($path);
    }

    /**
     * Get the file path for an instruction.
     *
     * @param string $name The instruction name
     * @return string The file path
     */
    private function getInstructionPath(string $name): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'Data' . \DIRECTORY_SEPARATOR .
            'DefaultInstructions' . \DIRECTORY_SEPARATOR . $name . '.md';
    }
}
