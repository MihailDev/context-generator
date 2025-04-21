<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;

/**
 * File-based implementation of the ProblemRepository interface.
 */
class FileProblemRepository implements ProblemRepository
{
    /**
     * @var string Directory where problem files are stored
     */
    private string $storageDir;

    public function __construct(string $storageDir)
    {
        $this->storageDir = rtrim($storageDir, '/');
        
        // Ensure storage directory exists
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(Problem $problem): void
    {
        $filePath = $this->getFilePath($problem->getId());
        $data = json_encode($problem->toArray(), JSON_PRETTY_PRINT);
        
        if ($data === false) {
            throw new \RuntimeException('Failed to encode problem data');
        }
        
        if (file_put_contents($filePath, $data) === false) {
            throw new \RuntimeException("Failed to write problem data to {$filePath}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findById(string $id): ?Problem
    {
        $filePath = $this->getFilePath($id);
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            throw new \RuntimeException("Failed to read problem data from {$filePath}");
        }
        
        $data = json_decode($content, true);
        
        if ($data === null) {
            throw new \RuntimeException("Failed to decode problem data from {$filePath}");
        }
        
        return Problem::fromArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $id): bool
    {
        return file_exists($this->getFilePath($id));
    }

    /**
     * {@inheritdoc}
     */
    public function listIds(): array
    {
        $files = glob($this->storageDir . '/*.json');
        
        if ($files === false) {
            return [];
        }
        
        return array_map(function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $files);
    }

    /**
     * Get the full file path for a problem ID.
     */
    private function getFilePath(string $id): string
    {
        return $this->storageDir . '/' . $id . '.json';
    }
}
