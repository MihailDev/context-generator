<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver;

use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Exceptions\ActionException;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Psr\Http\Message\ServerRequestInterface;

#[Tool(
    name: 'continue-last-problem',
    description: 'Continue a problem-solving step',
)]
class ContinueLastAction extends BaseAction
{
    /**
     * @throws ActionException
     * @throws \Throwable
     */
    #[Post(path: '/tools/call/continue-last-problem', name: 'tools.continue-last-problem')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing continue-last-problem tool');

        $problem = $this->problemService->getLastProblem();
        if (empty($problem)) {
            $this->sendError('No problems found');
        }

        try {
            $this->problemService->onContinue($problem);

            $handler = $this->problemService->getHandler($problem);

            // Return continue instructions
            return $handler->getContinueInstruction($problem)->toCallToolResult();
        } catch (\Throwable $e) {
            $this->logger->error('Error in continue or restore action', [
                'problem_id' => $problem->getId(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
