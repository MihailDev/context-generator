<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Exceptions\ActionException;

abstract class BaseProblemAction extends BaseAction
{
    /**
     * @throws ActionException
     */
    protected function getLastProblem(): Problem
    {
        $problem = $this->problemService->getLastProblem();

        if (empty($problem)) {
            throw new ActionException('No problem found');
        }

        return $problem;

    }
}
