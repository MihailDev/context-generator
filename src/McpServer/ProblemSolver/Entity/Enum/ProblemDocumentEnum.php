<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum;

/**
 * Represents different types of documents related to a problem.
 */
enum ProblemDocumentEnum: string
{
    case INFO = 'info';
    case CONTEXT = 'context';
}
