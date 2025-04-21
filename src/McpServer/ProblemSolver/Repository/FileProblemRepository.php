<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Spiral\Files\FilesInterface;

/**
 * File-based implementation of the ProblemRepository interface.
 */
class FileProblemRepository implements ProblemRepository
{
    public function __construct(
        private string $storageDir,
        private readonly FilesInterface $files,
    ) {
        $this->storageDir = \rtrim($storageDir, '/\\');

        if ($this->files->isDirectory($this->storageDir)) {
            $this->files->ensureDirectory($this->storageDir);
        }
    }

    public function save(Problem $problem): void
    {
        $filePath = $this->getFilePath($problem->getId());
        $data = \json_encode($problem->toArray(), JSON_PRETTY_PRINT);

        if ($data === false) {
            throw new \RuntimeException('Failed to encode problem data');
        }

        $this->files->write($filePath, $data, FilesInterface::RUNTIME, true);
    }

    public function findById(string $id): ?Problem
    {
        $filePath = $this->getFilePath($id);

        if (!$this->files->exists($filePath)) {
            return null;
        }

        $content = $this->files->read($filePath);

        $data = \json_decode($content, true);

        if ($data === null) {
            throw new \RuntimeException("Failed to decode problem data from {$filePath}");
        }

        return Problem::fromArray($data);
    }

    public function exists(string $id): bool
    {
        return $this->files->exists($this->getFilePath($id));
    }

    public function listIds(): array
    {
        $files = \glob($this->storageDir . '/*', \GLOB_ONLYDIR);

        if ($files === false) {
            return [];
        }

        return $files;
    }

    public function getProblemDirectory(string $id): string
    {
        return $this->storageDir . \DIRECTORY_SEPARATOR . $id;
    }

    /**
     * Get the full file path for a problem ID.
     */
    private function getFilePath(string $id): string
    {
        return $this->getProblemDirectory($id) . \DIRECTORY_SEPARATOR . 'problem.json';
    }
}
