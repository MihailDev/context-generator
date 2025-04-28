<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Exceptions;

class ActionException extends McpException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 500);
    }
}
