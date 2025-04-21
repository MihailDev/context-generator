<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver;

use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Mcp\Types\TextContent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

#[Tool(
    name: 'continue-problem',
    description: 'Continue a problem-solving step',
)]
#[InputSchema(
    name: 'problem_id',
    type: 'string',
    description: 'Problem ID',
    required: true,
)]
final readonly class ContinueAction
{
    public function __construct(
        private LoggerInterface $logger,
        private ProblemService $problemService,
        private InstructionService $instructionService,
    ) {}

    #[Post(path: '/tools/call/continue-or-restore', name: 'tools.continue-or-restore')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing continue-or-restore tool');

        // Get params from the parsed body for POST requests
        $parsedBody = $request->getParsedBody();

        if (!isset($parsedBody['problem_id'])) {
            return new CallToolResult([
                new TextContent(
                    text: 'Error: Missing required parameter: problem_id',
                ),
            ], isError: true);
        }

        $problemId = $parsedBody['problem_id'];

        try {
            // Get the problem and prepare for continuation
            $problem = $this->problemService->getProblem($problemId);
            $this->problemService->onContinue($problem);

            $handler = $this->problemService->getHandler($problem);

            // Return continue instructions
            return new CallToolResult($handler->getContinueInstruction($problem));
        } catch (\Throwable $e) {
            $this->logger->error('Error in continue or restore action', [
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
