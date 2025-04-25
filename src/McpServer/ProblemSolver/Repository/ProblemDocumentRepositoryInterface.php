<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemDocumentEnum;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;

/**
 * Interface for problem document repository implementations.
 */
interface ProblemDocumentRepositoryInterface
{
    /**
     * Save a document for a problem.
     *
     * @param Problem $problem The problem to save the document for
     * @param ProblemDocumentEnum $category Document category
     * @param string $name Document name
     * @param string $content Document content
     * @return bool Whether the save was successful
     */
    public function save(Problem $problem, ProblemDocumentEnum $category, string $name, string $content): bool;

    /**
     * Get a document for a problem.
     *
     * @param Problem $problem The problem to get the document for
     * @param ProblemDocumentEnum $category Document category
     * @param string $name Document name
     * @return string|null The document content if found, null otherwise
     */
    public function get(Problem $problem, ProblemDocumentEnum $category, string $name): ?string;

    /**
     * Check if a document exists for a problem.
     *
     * @param Problem $problem The problem to check
     * @param ProblemDocumentEnum $category Document category
     * @param string $name Document name
     * @return bool Whether the document exists
     */
    public function exists(Problem $problem, ProblemDocumentEnum $category, string $name): bool;

    /**
     * List all documents for a problem in a specific category.
     *
     * @param Problem $problem The problem to list documents for
     * @param ProblemDocumentEnum $category Document category
     * @return array<string> List of document names
     */
    public function listDocuments(Problem $problem, ProblemDocumentEnum $category): array;

    /**
     * Delete a document for a problem.
     *
     * @param Problem $problem The problem to delete the document for
     * @param ProblemDocumentEnum $category Document category
     * @param string $name Document name
     * @return bool Whether the deletion was successful
     */
    public function delete(Problem $problem, ProblemDocumentEnum $category, string $name): bool;

    public function setBrainstormingDraft(
        Problem $problem,
        string $content,
    ): bool;

    public function getBrainstormingDraft(
        Problem $problem,
    ): ?string;

    public function setLastProblem(string $getId): bool;

    public function getLastProblem(): string;
}
