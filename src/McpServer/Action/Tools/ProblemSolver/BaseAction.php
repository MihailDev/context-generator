<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Exceptions\ActionException;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

abstract class BaseAction
{
    protected array $requiredParameters = [];

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly ProblemService $problemService,
    ) {}

    /**
     * @throws ActionException
     */
    protected function sendError(string $message): void
    {
        throw new ActionException($message);
    }

    /**
     * @throws ActionException
     */
    protected function validateRequiredParameters(ServerRequestInterface $request, array $params = []): array
    {
        $this->logger->info("Request {$request->getUri()}: Validating required parameters");
        $parsedBody = $request->getParsedBody();
        foreach ($this->requiredParameters as $param) {
            if (empty($parsedBody[$param])) {
                $this->sendError("Missing required parameter: $param");
            }
        }

        foreach ($params as $param) {
            if (empty($parsedBody[$param])) {
                $this->sendError("Missing required parameter: $param");
            }
        }

        return $parsedBody;
    }
}
