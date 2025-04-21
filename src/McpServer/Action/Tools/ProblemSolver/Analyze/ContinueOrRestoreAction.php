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
    name: 'continue-or-restore',
    description: 'Continue or restore a problem-solving step',
)]
#[InputSchema(
    name: 'problem_id',
    type: 'string',
    description: 'Problem ID',
    required: true,
)]
#[InputSchema(
    name: 'restore_reason',
    type: 'string',
    description: 'Reason for restoring the step (if applicable)',
    required: false,
)]
final readonly class ContinueOrRestoreAction
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
        $restoreReason = $parsedBody['restore_reason'] ?? null;

        try {
            // Get the problem
            $problem = $this->problemService->getProblem($problemId);
            
            // Format problem context for display
            $formattedContext = $this->instructionService->formatProblemContext($problem->getContext());
            
            // Prepare response based on current step
            $currentStep = $problem->getCurrentStep();
            
            $response = [
                'success' => true,
                'problem' => [
                    'id' => $problem->getId(),
                    'original_problem' => $problem->getOriginalProblem(),
                    'type' => $problem->getType(),
                    'default_project' => $problem->getDefaultProject(),
                ],
                'brainstorming_draft' => $problem->getBrainstormingDraft(),
                'problem_context_formatted' => $formattedContext,
            ];
            
            // Add appropriate instructions based on current step
            $response['step_instructions'] = $this->instructionService->getStepInstructions($currentStep);
            
            // Add restore reason if provided
            if ($restoreReason !== null) {
                $response['restore_reason'] = $restoreReason;
                $response['restore_instruction'] = "Restoration note: {$restoreReason}. Please continue from where you left off.";
            }
            
            return new CallToolResult([
                new TextContent(
                    text: \json_encode($response),
                ),
            ]);
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
