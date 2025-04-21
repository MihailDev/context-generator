<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

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

        try {
            // Update the problem with brainstorming information
            $problem = $this->problemService->updateProblemDetails(
                $problemId,
                $problemType,
                $defaultProject,
                $brainstormingDraft,
                $problemContext
            );

            // Return success response with pause instructions
            return new CallToolResult([
                new TextContent(
                    text: \json_encode([
                        'success' => true,
                        'problem_id' => $problem->getId(),
                        'instructions' => $this->instructionService->getPauseInstructions(),
                    ]),
                ),
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
