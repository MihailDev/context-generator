<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Type\ProblemActionInstructions;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemDocumentRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Repository\ProblemRepositoryInterface;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemContextService;
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
        private ProblemContextService $contextService,
    ) {}


    public function startInstructions(Problem $problem): ProblemActionInstructions
    {

        $instructions = new ProblemActionInstructions();
        $instructions->add(
            $this->instructionService->problemInfo($problem),
        );

        $instructions->add(
            $this->instructionService->getFirstAnalyzeInstruction($problem),
        );

        return $instructions;

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
        $this->problemRepository->save($problem);

        $instructions = new ProblemActionInstructions();

        $instructions->add($this->instructionService->problemInfo($problem));

        if ($problem->isContextChanged()) {
            $instructions->add($this->contextService->generate($problem->getContext()));
        }
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
