<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver;

use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Exceptions\ActionException;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Psr\Http\Message\ServerRequestInterface;

#[Tool(
    name: 'problem-continue-selected',
    description: 'Continue selected problem',
)]
#[InputSchema(
    name: 'problem_id',
    type: 'string',
    description: 'Problem ID (pattern: ((?<!([A-Z]{1,10})-?)[A-Z]+-\d+))',
    required: true,
)]
final class ContinueAction extends BaseAction
{
    /**
     * @throws ActionException
     * @throws \Throwable
     */
    #[Post(path: '/tools/call/problem-continue-selected', name: 'tools.problem-continue-selected')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing continue-or-restore tool');

        // Get params from the parsed body for POST requests
        $parsedBody = $this->validateRequiredParameters($request, ['problem_id']);

        $problemId = $parsedBody['problem_id'];

        // Get the problem and prepare for continuation
        $problem = $this->problemService->getProblem($problemId);

        try {
            $this->problemService->onContinue($problem);

            $handler = $this->problemService->getHandler($problem);

            // Return continue instructions
            return $handler->getContinueInstruction($problem)->toCallToolResult();
        } catch (\Throwable $e) {
            $this->logger->error('Error in continue selected action', [
                'problem_id' => $problemId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
