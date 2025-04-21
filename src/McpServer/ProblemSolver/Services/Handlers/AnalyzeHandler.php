<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemDocumentRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Mcp\Types\TextContent;
use Psr\Log\LoggerInterface;

/**
 * Handler for the Analyze step in problem solving.
 * Handles first analysis of the problem and draft preparation.
 */
final readonly class AnalyzeHandler implements StepHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ProblemService $problemService,
        private ProblemRepositoryInterface $problemRepository,
        private InstructionService $instructionService,
        private ProblemDocumentRepositoryInterface $problemDocumentRepository,
    ) {}

    /**
     * @return TextContent[] Instructions for the next steps
     */
    public function startInstructions(): array {}

    public function instructionsOnWrongAction(string $message): ProblemActionInstructions
    {
        // TODO: Implement instructionsOnWrongAction() method.
    }

    public function saveBrainstormingDraft(
        Problem $problem,
        string  $problemType,
        string  $defaultProject,
        string  $brainstormingDraft,
        array   $context,
    ): ProblemActionInstructions {
        $problem->setType($problemType)
            ->setDefaultProject($defaultProject)
            ->setContext($context);

        $this->problemDocumentRepository->setBrainstormingDraft($problem, $brainstormingDraft);

        $problem->setCurrentStep(ProblemStep::ANALYZE);
        $instructions = new ProblemActionInstructions();
        if ($this->problemRepository->save($problem)) {

            $instructions->add();

        } else {
            throw new \Exception('Error saving problem');
        }

        return $instructions;
    }

    public function approveBrainstormingDraft(Problem $problem): ProblemActionInstructions {}

    public function getContinueInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getContinueInstruction() method.
    }
}
