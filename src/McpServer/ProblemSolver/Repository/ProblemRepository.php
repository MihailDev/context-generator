<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;

/**
 * Interface for problem repository implementations.
 */
interface ProblemRepository
{
    /**
     * Save a problem to the repository.
     */
    public function save(Problem $problem): void;

    /**
     * Find a problem by its identifier.
     *
     * @param string $id Problem identifier
     * @return Problem|null The problem if found, null otherwise
     */
    public function findById(string $id): ?Problem;

    /**
     * Check if a problem with the given ID exists.
     */
    public function exists(string $id): bool;

    /**
     * List all problem IDs in the repository.
     *
     * @return array<string> List of problem IDs
     */
    public function listIds(): array;
}
