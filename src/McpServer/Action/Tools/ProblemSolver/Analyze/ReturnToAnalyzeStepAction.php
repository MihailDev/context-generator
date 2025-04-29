<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

use Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\BaseReturnToStepAction;
use Butschster\ContextGenerator\McpServer\Attribute\InputSchema;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Psr\Http\Message\ServerRequestInterface;

#[Tool(
    name: 'problem-return-to-analyze-step',
    description: 'Return to the analyze step for a problem',
)]
#[InputSchema(
    name: 'return_reason',
    type: 'string',
    description: 'Return Reason',
    required: true,
)]
final class ReturnToAnalyzeStepAction extends BaseReturnToStepAction
{
    /**
     * @throws \Throwable
     */
    #[Post(path: '/tools/call/problem-return-to-analyze-step', name: 'tools.problem-return-to-analyze-step')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        return $this->process($request, ProblemStep::ANALYZE);
    }
}
