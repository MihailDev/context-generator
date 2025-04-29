<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

use Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\BaseProblemAction;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\AnalyzeHandler;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Psr\Http\Message\ServerRequestInterface;

#[Tool(
    name: 'problem-analyze-approve-draft',
    description: 'Approve brainstorming draft',
)]
final class ApproveBrainstormingDraftAction extends BaseProblemAction
{
    #[Post(path: '/tools/call/problem-analyze-approve-draft', name: 'tools.problem-analyze-approve-draft')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing problem-analyze-approve-draft tool');

        // Get params from the parsed body for POST requests
        // $parsedBody = $this->validateRequiredParameters($request, []);

        $problem = $this->getLastProblem();

        try {
            $handler = $this->problemService->getHandler($problem);

            \assert($handler instanceof AnalyzeHandler);

            $instructions = $handler->approveBrainstormingDraft(
                $problem,
            );

            return $instructions->toCallToolResult();

        } catch (\Throwable $e) {
            $this->logger->error('Error approve brainstorming draft', [
                'problem_id' => $problem->getId(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
