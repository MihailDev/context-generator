<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\AnalyzeHandler;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Butschster\ContextGenerator\McpServer\ProjectService\ProjectServiceInterface;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Mcp\Types\TextContent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

#[Tool(
    name: 'add-problem',
    description: 'Add a new problem to be solved',
)]
#[InputSchema(
    name: 'original_problem',
    type: 'string',
    description: 'Original description of the problem',
    required: true,
)]
#[InputSchema(
    name: 'problem_id',
    type: 'string',
    description: 'Custom problem ID (generated if not provided)',
    required: false,
)]
final readonly class AddProblemAction
{
    public function __construct(
        private LoggerInterface    $logger,
        private ProblemService     $problemService,
        private InstructionService $instructionService,
        private ProjectServiceInterface $projectService,
    ) {}

    #[Post(path: '/tools/call/add-problem', name: 'tools.add-problem')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing add-problem tool');

        // Get params from the parsed body for POST requests
        $parsedBody = $request->getParsedBody();

        if (!isset($parsedBody['original_problem'])) {
            return new CallToolResult([
                new TextContent(
                    text: 'Error: Missing required parameter: original_problem',
                ),
            ], isError: true);
        }

        $originalProblem = $parsedBody['original_problem'];
        $problemId = $parsedBody['problem_id'] ?? null;

        if (!empty($problemId) && $this->problemService->problemExists($problemId)) {
            $this->logger->info('Problem with ID already exists', []);
            $problem = $this->problemService->getProblem($problemId);

            $handler = $this->problemService->getHandler($problem);

            return new CallToolResult($handler->instructionsOnWrongAction('Problem with ID already exists')->getCallContents(), true);
        }

        try {
            // Create a new problem
            $problem = $this->problemService->createProblem(
                $originalProblem,
                $problemId,
                $this->projectService->getProjectName(),
            );

            $analyzeHandler = $this->problemService->getHandler($problem);

            \assert($analyzeHandler instanceof AnalyzeHandler);

            // Return success response with instructions
            return new CallToolResult($analyzeHandler->startInstructions());
        } catch (\Throwable $e) {
            $this->logger->error('Error adding problem', [
                'original_problem' => $originalProblem,
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
