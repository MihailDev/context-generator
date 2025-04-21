<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Mcp\Types\TextContent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

#[Tool(
    name: 'save-brainstorming-draft',
    description: 'Save a brainstorming draft for a problem',
)]
#[InputSchema(
    name: 'problem_id',
    type: 'string',
    description: 'Problem ID',
    required: true,
)]
#[InputSchema(
    name: 'problem_type',
    type: 'string',
    description: 'Type of the problem (feature, bug, research, refactoring)',
    required: true,
)]
#[InputSchema(
    name: 'default_project',
    type: 'string',
    description: 'Default project related to the problem',
    required: true,
)]
#[InputSchema(
    name: 'brainstorming_draft',
    type: 'string',
    description: 'Draft guide for brainstorming',
    required: true,
)]
#[InputSchema(
    name: 'problem_context',
    type: 'object',
    description: 'Problem context with related information',
    required: false,
)]
#[InputSchema(
    name: 'approved_by_owner',
    type: 'boolean',
    description: 'Approved by owner',
    required: false,
)]
final readonly class SaveBrainstormingDraftAction
{
    public function __construct(
        private LoggerInterface $logger,
        private ProblemService $problemService,
        private InstructionService $instructionService,
    ) {}

    #[Post(path: '/tools/call/save-brainstorming-draft', name: 'tools.save-brainstorming-draft')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing save-brainstorming-draft tool');

        // Get params from the parsed body for POST requests
        $parsedBody = $request->getParsedBody();

        // Validate required parameters
        $requiredParams = ['problem_id', 'problem_type', 'default_project', 'brainstorming_draft'];
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
        $problemType = $parsedBody['problem_type'];
        $defaultProject = $parsedBody['default_project'];
        $brainstormingDraft = $parsedBody['brainstorming_draft'];
        $problemContext = $parsedBody['problem_context'] ?? [];
        $approvedByOwner = !empty($parsedBody['approved_by_owner']);

        try {
            $problem = $this->problemService->getProblem($problemId);

            $this->problemService->checkStep($problem, ProblemStep::ANALYZE);

            if ($approvedByOwner) {
                $this->problemService->startBrainstorming(
                    $problem,
                    $problemType,
                    $defaultProject,
                    $brainstormingDraft,
                    $problemContext,
                );

                // Return success response with pause instructions
                return new CallToolResult([
                    $this->instructionService->getAnalyzeCompleteInstructions($problem),
                    $this->instructionService->getPauseInstructions($problem),
                ]);
            }

            $this->problemService->saveAnalyze(
                $problem,
                $problemType,
                $defaultProject,
                $brainstormingDraft,
                $problemContext,
            );


            return new CallToolResult([
                $this->instructionService->getAnalyzeInstructions($problem),
            ]);

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
