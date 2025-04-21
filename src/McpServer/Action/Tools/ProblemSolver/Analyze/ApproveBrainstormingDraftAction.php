<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\AnalyzeHandler;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Mcp\Types\TextContent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

#[Tool(
    name: 'approve-brainstorming-draft',
    description: 'Save a brainstorming draft for a problem',
)]
#[InputSchema(
    name: 'problem_id',
    type: 'string',
    description: 'Problem ID',
    required: true,
)]

final readonly class ApproveBrainstormingDraftAction
{
    public function __construct(
        private LoggerInterface $logger,
        private ProblemService $problemService,
    ) {}

    #[Post(path: '/tools/call/approve-brainstorming-draft', name: 'tools.approve-brainstorming-draft')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing approve-brainstorming-draft tool');

        // Get params from the parsed body for POST requests
        $parsedBody = $request->getParsedBody();

        // Validate required parameters
        $requiredParams = ['problem_id'];
        foreach ($requiredParams as $param) {
            if (!isset($parsedBody[$param])) {
                return new CallToolResult([
                    new TextContent(
                        text: \sprintf("Error: Missing required parameter: %s", $param),
                    ),
                ], isError: true);
            }
        }

        $problemId = $parsedBody['problem_id'];

        try {
            $problem = $this->problemService->getProblem($problemId);

            $handler = $this->problemService->getHandler($problem);

            \assert($handler instanceof AnalyzeHandler);

            $instructions = $handler->approveBrainstormingDraft(
                $problem,
            );

            return new CallToolResult($instructions->getCallContents());

        } catch (\Throwable $e) {
            $this->logger->error('Error saving brainstorming draft', [
                'problem_id' => $problemId,
                'error' => $e->getMessage(),
            ]);

            return new CallToolResult([
                new TextContent(
                    text: 'Error: ' . $e->getMessage(),
                ),
            ], isError: true);
        }
    }
}
