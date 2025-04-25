<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

use Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\BaseAction;
use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\AnalyzeHandler;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Butschster\ContextGenerator\McpServer\ProjectService\ProjectServiceInterface;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
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
final class AddProblemAction extends BaseAction
{
    public function __construct(
        LoggerInterface $logger,
        ProblemService $problemService,
        private readonly ProjectServiceInterface $projectService,
    ) {
        parent::__construct($logger, $problemService);
    }

    #[Post(path: '/tools/call/add-problem', name: 'tools.add-problem')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing add-problem tool');

        $parsedBody = $this->validateRequiredParameters($request, ['original_problem']);

        $originalProblem = $parsedBody['original_problem'];
        $problemId = $parsedBody['problem_id'] ?? null;

        if (!empty($problemId) && $this->problemService->problemExists($problemId)) {
            $this->sendError('Problem with ID ' . $problemId . ' already exists.');
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
            return $analyzeHandler->startInstructions()->toCallToolResult();
        } catch (\Throwable $e) {
            $this->logger->error('Error adding problem', [
                'original_problem' => $originalProblem,
                'problem_id' => $problemId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
