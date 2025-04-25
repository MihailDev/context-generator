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
    name: 'approve-brainstorming-draft',
    description: 'Save a brainstorming draft for a problem',
)]
final class ApproveBrainstormingDraftAction extends BaseProblemAction
{
    #[Post(path: '/tools/call/approve-brainstorming-draft', name: 'tools.approve-brainstorming-draft')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing approve-brainstorming-draft tool');

        // Get params from the parsed body for POST requests
        $parsedBody = $this->validateRequiredParameters($request, []);

        $problemId = $parsedBody['problem_id'];

        try {
            $problem = $this->problemService->getProblem($problemId);

            $handler = $this->problemService->getHandler($problem);

            \assert($handler instanceof AnalyzeHandler);

            $instructions = $handler->approveBrainstormingDraft(
                $problem,
            );

            return $instructions->toCallToolResult();

        } catch (\Throwable $e) {
            $this->logger->error('Error saving brainstorming draft', [
                'problem_id' => $problemId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
