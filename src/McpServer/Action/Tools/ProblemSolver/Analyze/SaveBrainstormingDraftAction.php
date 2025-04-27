<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

use Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\BaseProblemAction;
use Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\InputSchema\ContextInputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers\AnalyzeHandler;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Psr\Http\Message\ServerRequestInterface;

#[Tool(
    name: 'save-brainstorming-draft',
    description: 'Save a brainstorming draft',
)]
#[InputSchema(
    name: 'problem_type',
    type: 'string',
    description: 'Type of the problem (feature, bug, research, refactoring)',
    required: true,
    enum: ['feature', 'bug', 'research', 'refactoring'],
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
    name: 'brainstorming_context',
    type: 'object',
    description: 'Brainstorming context with related information',
    required: false,
    properties: ContextInputSchema::PROPERTIES,
)]

final class SaveBrainstormingDraftAction extends BaseProblemAction
{
    #[Post(path: '/tools/call/save-brainstorming-draft', name: 'tools.save-brainstorming-draft')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        $this->logger->info('Processing save-brainstorming-draft tool');

        $parsedBody = $this->validateRequiredParameters($request, [ 'problem_type', 'default_project', 'brainstorming_draft']);

        $problemId = $parsedBody['problem_id'];
        $problemType = $parsedBody['problem_type'];
        $defaultProject = $parsedBody['default_project'];
        $brainstormingDraft = $parsedBody['brainstorming_draft'];
        $context = $parsedBody['brainstorming_context'] ?? [];

        try {
            $problem = $this->problemService->getProblem($problemId);

            $handler = $this->problemService->getHandler($problem);

            \assert($handler instanceof AnalyzeHandler);

            $instructions = $handler->saveBrainstormingDraft(
                $problem,
                $problemType,
                $defaultProject,
                $brainstormingDraft,
                $context,
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
