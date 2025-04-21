<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\FileInstructionRepository;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\FileProblemDocumentRepository;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\FileProblemRepository;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\InstructionRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemDocumentRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemRepositoryInterface;
use Spiral\Boot\Bootloader\Bootloader;

/**
 * Bootloader for ProblemSolver module.
 */
final class ProblemSolverBootloader extends Bootloader
{
    public function defineSingletons(): array
    {
        return [
            InstructionRepositoryInterface::class => FileInstructionRepository::class,
            ProblemRepositoryInterface::class => FileProblemRepository::class,
            ProblemDocumentRepositoryInterface::class => FileProblemDocumentRepository::class,
        ];
    }
}
