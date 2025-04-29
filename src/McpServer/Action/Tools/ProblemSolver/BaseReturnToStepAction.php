<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver;

use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Mcp\Types\CallToolResult;
use Psr\Http\Message\ServerRequestInterface;

#[InputSchema(
    name: 'return_reason',
    type: 'string',
    description: 'Return Reason',
    required: true,
)]
abstract class BaseReturnToStepAction extends BaseProblemAction
{
    public function process(
        ServerRequestInterface $request,
        ProblemStep            $step,
    ): CallToolResult {
        $this->logger->info('Processing return-to-step ' . $step->value . ' tool');

        $parsedBody = $this->validateRequiredParameters($request, ['return_reason']);
        $problem = $this->getLastProblem();

        try {
            $this->problemService->restoreToStep(
                $problem,
                $step,
                $parsedBody['return_reason'],
            );

            $handler = $this->problemService->getHandler($problem);

            return $handler->getContinueInstruction($problem)->toCallToolResult();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error in restore action',
                [
                    'problem_id' => $problem->getId(),
                    'error' => $e->getMessage(),
                ],
            );

            throw $e;
        }
    }

    abstract public function __invoke(ServerRequestInterface $request): CallToolResult;
}
