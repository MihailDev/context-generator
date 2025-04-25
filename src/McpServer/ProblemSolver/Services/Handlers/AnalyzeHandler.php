<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemDocumentRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
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
        private ProblemRepositoryInterface $problemRepository,
        private InstructionService $instructionService,
        private ProblemDocumentRepositoryInterface $problemDocumentRepository,
        private BarnstormingHandler $barnstormingHandler,
    ) {}

    /**
     * @return TextContent[] Instructions for the next steps
     */
    public function startInstructions(): ProblemActionInstructions {}

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
        $this->problemRepository->save($problem);

        $instructions->add($this->instructionService->getAnalyzeInstruction($problem));

        return $instructions;
    }

    public function approveBrainstormingDraft(Problem $problem): ProblemActionInstructions
    {
        $problem->setCurrentStep(ProblemStep::BRAINSTORMING);



        $this->problemRepository->save($problem);
        $this->barnstormingHandler->addBarnstorming($problem);

        return $this->getFinishInstruction($problem);
    }

    public function getContinueInstruction(Problem $problem): ProblemActionInstructions {}

    public function getFinishInstruction(Problem $problem): ProblemActionInstructions
    {
        // TODO: Implement getFinishInstruction() method.
    }

    public function createProblem(Problem $problem): Problem
    {

        $this->problemRepository->save($problem);

        return $problem;
    }
}
