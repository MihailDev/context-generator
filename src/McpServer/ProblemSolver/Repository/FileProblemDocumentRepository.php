<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemDocumentEnum;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Spiral\Files\FilesInterface;

/**
 * File-based implementation of the ProblemDocumentRepository interface.
 */
readonly class FileProblemDocumentRepository implements ProblemDocumentRepositoryInterface
{
    public function __construct(
        private ProblemRepositoryInterface $problemRepository,
        private FilesInterface             $files,
    ) {}

    public function save(Problem $problem, ProblemDocumentEnum $category, string $name, string $content): bool
    {
        $filePath = $this->getDocumentPath($problem, $category, $name);
        $dirPath = \dirname($filePath);

        if (!$this->files->isDirectory($dirPath)) {
            $this->files->ensureDirectory($dirPath);
        }

        return $this->files->write($filePath, $content, FilesInterface::RUNTIME, true);
    }

    public function get(Problem $problem, ProblemDocumentEnum $category, string $name): ?string
    {
        $filePath = $this->getDocumentPath($problem, $category, $name);

        if (!$this->files->exists($filePath)) {
            return null;
        }

        return $this->files->read($filePath);
    }

    public function exists(Problem $problem, ProblemDocumentEnum $category, string $name): bool
    {
        return $this->files->exists($this->getDocumentPath($problem, $category, $name));
    }

    public function listDocuments(Problem $problem, ProblemDocumentEnum $category): array
    {
        $dirPath = $this->getCategoryPath($problem, $category);

        if (!$this->files->isDirectory($dirPath)) {
            return [];
        }

        $files = $this->files->getFiles($dirPath);
        $documents = [];

        foreach ($files as $file) {
            $documents[] = \basename($file);
        }

        return $documents;
    }

    public function delete(Problem $problem, ProblemDocumentEnum $category, string $name): bool
    {
        $filePath = $this->getDocumentPath($problem, $category, $name);

        if (!$this->files->exists($filePath)) {
            return false;
        }

        $this->files->delete($filePath);
        return true;
    }

    public function setBrainstormingDraft(
        Problem $problem,
        string $content,
    ): bool {
        return $this->save($problem, ProblemDocumentEnum::INFO, Problem::DOCUMENT_BRAINSTORMING_DRAFT, $content);
    }

    public function getBrainstormingDraft(
        Problem $problem,
    ): ?string {
        return $this->get($problem, ProblemDocumentEnum::INFO, Problem::DOCUMENT_BRAINSTORMING_DRAFT);
    }

    /**
     * Get the base directory for a problem's documents.
     */
    private function getDocumentsDirectory(Problem $problem): string
    {
        return $this->problemRepository->getProblemDirectory($problem->getId()) . \DIRECTORY_SEPARATOR . 'documents';
    }

    /**
     * Get the directory path for a specific document category.
     */
    private function getCategoryPath(Problem $problem, ProblemDocumentEnum $category): string
    {
        return $this->getDocumentsDirectory($problem) . \DIRECTORY_SEPARATOR . $category->value;
    }

    /**
     * Get the full file path for a document.
     */
    private function getDocumentPath(Problem $problem, ProblemDocumentEnum $category, string $name): string
    {
        return $this->getCategoryPath($problem, $category) . \DIRECTORY_SEPARATOR . $name;
    }
}
