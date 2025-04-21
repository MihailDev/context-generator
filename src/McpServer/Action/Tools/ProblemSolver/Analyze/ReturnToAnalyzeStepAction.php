<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\Analyze;

use Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\BaseReturnToStepAction;
use Butschster\ContextGenerator\McpServer\Attribute\Tool;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Post;
use Mcp\Types\CallToolResult;
use Psr\Http\Message\ServerRequestInterface;

#[Tool(
    name: 'return-to-analyze-step',
    description: 'Return to the analyze step for a problem',
)]
final class ReturnToAnalyzeStepAction extends BaseReturnToStepAction
{
    #[Post(path: '/tools/call/return-to-analyze-step', name: 'tools.return-to-analyze-step')]
    public function __invoke(ServerRequestInterface $request): CallToolResult
    {
        return $this->process($request, ProblemStep::ANALYZE);
    }
}
