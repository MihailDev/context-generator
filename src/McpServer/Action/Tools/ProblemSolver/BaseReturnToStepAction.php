<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver;

use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\WorkflowStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Mcp\Types\CallToolResult;
use Mcp\Types\TextContent;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

#[InputSchema(
    name: 'problem_id',
    type: 'string',
    description: 'Problem ID',
    required: true,
)]
#[InputSchema(
    name: 'return_reason',
    type: 'string',
    description: 'Return Reason',
    required: true,
)]
abstract class BaseReturnToStepAction
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProblemService $problemService,
        private readonly InstructionService $instructionService,
    ) {}

    public function process(
        ServerRequestInterface $request,
        WorkflowStep           $step,
    ): CallToolResult {
        $this->logger->info('Processing return-to-step ' . $step->value . ' tool');

        // Get params from the parsed body for POST requests
        $parsedBody = $request->getParsedBody();

        if (empty($parsedBody['problem_id'])) {
            return new CallToolResult(
                [
                    new TextContent(
                        text: 'Error: Missing required parameter: problem_id',
                    ),
                ],
                isError: true,
            );
        }

        if (empty($parsedBody['return_reason'])) {
            return new CallToolResult(
                [
                    new TextContent(
                        text: 'Error: Missing required parameter: return_reason',
                    ),
                ],
                isError: true,
            );
        }

        $problemId = $parsedBody['problem_id'];

        try {
            $problem = $this->problemService->getProblem($problemId);
            $this->problemService->restoreToStep(
                $problem,
                $step,
                $parsedBody['return_reason'],
            );

            return new CallToolResult(
                [
                    $this->instructionService->getContinueInstruction($problem),
                ],
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error in restore action',
                [
                    'problem_id' => $problemId,
                    'error' => $e->getMessage(),
                ],
            );

            return new CallToolResult(
                [
                    new TextContent(
                        text: 'Error: ' . $e->getMessage(),
                    ),
                ],
                isError: true,
            );
        }
    }

    abstract public function __invoke(ServerRequestInterface $request): CallToolResult;
}
