<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\WorkflowStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;

/**
 * Service for managing problem solving workflow.
 */
final readonly class ProblemWorkflowService
{
    public function __construct(
        private ProblemService $problemService,
        private InstructionService $instructionService,
    ) {}

    /**
     * Move a problem to the next step in the workflow.
     *
     * @param string $problemId Problem ID
     * @return Problem The updated problem
     * @throws \InvalidArgumentException If problem not found or cannot move to next step
     */
    public function moveToNextStep(string $problemId): Problem
    {
        $problem = $this->problemService->getProblem($problemId);
        $currentStepNumber = $problem->getCurrentStep();

        // Map numeric step to enum value
        $currentStep = $this->getWorkflowStepFromNumber($currentStepNumber);

        // Get next step
        $nextStep = $currentStep->next();
        if ($nextStep === null) {
            throw new \InvalidArgumentException("Problem is already at the final step");
        }

        // Update problem with new step
        $problem->setCurrentStep($this->getNumberFromWorkflowStep($nextStep));
        $this->problemService->save($problem);

        return $problem;
    }

    /**
     * Move a problem to the previous step in the workflow.
     *
     * @param string $problemId Problem ID
     * @return Problem The updated problem
     * @throws \InvalidArgumentException If problem not found or cannot move to previous step
     */
    public function moveToPreviousStep(string $problemId): Problem
    {
        $problem = $this->problemService->getProblem($problemId);
        $currentStepNumber = $problem->getCurrentStep();

        // Map numeric step to enum value
        $currentStep = $this->getWorkflowStepFromNumber($currentStepNumber);

        // Get previous step
        $previousStep = $currentStep->previous();
        if ($previousStep === null) {
            throw new \InvalidArgumentException("Problem is already at the first step");
        }

        // Update problem with new step
        $problem->setCurrentStep($this->getNumberFromWorkflowStep($previousStep));
        $this->problemService->save($problem);

        return $problem;
    }

    /**
     * Move a problem to a specific step in the workflow.
     *
     * @param string $problemId Problem ID
     * @param WorkflowStep $targetStep Target step
     * @return Problem The updated problem
     * @throws \InvalidArgumentException If problem not found or cannot move to target step
     */
    public function moveToStep(string $problemId, WorkflowStep $targetStep): Problem
    {
        $problem = $this->problemService->getProblem($problemId);

        // Update problem with new step
        $problem->setCurrentStep($this->getNumberFromWorkflowStep($targetStep));
        $this->problemService->save($problem);

        return $problem;
    }

    /**
     * Check if a problem can move to the next step.
     *
     * @param string $problemId Problem ID
     * @return bool True if the problem can move to the next step
     * @throws \InvalidArgumentException If problem not found
     */
    public function canMoveToNextStep(string $problemId): bool
    {
        $problem = $this->problemService->getProblem($problemId);
        $currentStepNumber = $problem->getCurrentStep();

        // Map numeric step to enum value
        $currentStep = $this->getWorkflowStepFromNumber($currentStepNumber);

        // Check if next step exists
        return $currentStep->next() !== null;
    }

    /**
     * Check if a problem can move to the previous step.
     *
     * @param string $problemId Problem ID
     * @return bool True if the problem can move to the previous step
     * @throws \InvalidArgumentException If problem not found
     */
    public function canMoveToPreviousStep(string $problemId): bool
    {
        $problem = $this->problemService->getProblem($problemId);
        $currentStepNumber = $problem->getCurrentStep();

        // Map numeric step to enum value
        $currentStep = $this->getWorkflowStepFromNumber($currentStepNumber);

        // Check if previous step exists
        return $currentStep->previous() !== null;
    }

    /**
     * Get the current step for a problem.
     *
     * @param string $problemId Problem ID
     * @return WorkflowStep The current step
     * @throws \InvalidArgumentException If problem not found
     */
    public function getCurrentStep(string $problemId): WorkflowStep
    {
        $problem = $this->problemService->getProblem($problemId);
        return $this->getWorkflowStepFromNumber($problem->getCurrentStep());
    }

    /**
     * Get instructions for the current step of a problem.
     *
     * @param string $problemId Problem ID
     * @param array $context Additional context for instructions
     * @return string Instructions for the current step
     * @throws \InvalidArgumentException If problem not found
     */
    public function getCurrentStepInstructions(string $problemId, array $context = []): string
    {
        $problem = $this->problemService->getProblem($problemId);
        return $this->instructionService->getStepInstructions($problem->getCurrentStep(), $context);
    }

    /**
     * Check if a problem is at a specific step.
     *
     * @param string $problemId Problem ID
     * @param WorkflowStep $step Step to check
     * @return bool True if the problem is at the given step
     * @throws \InvalidArgumentException If problem not found
     */
    public function isAtStep(string $problemId, WorkflowStep $step): bool
    {
        $problem = $this->problemService->getProblem($problemId);
        $currentStep = $this->getWorkflowStepFromNumber($problem->getCurrentStep());

        return $currentStep === $step;
    }

    /**
     * Validate if a problem can be moved to a specific step.
     *
     * @param string $problemId Problem ID
     * @param WorkflowStep $targetStep Target step
     * @return bool True if the move is valid
     * @throws \InvalidArgumentException If problem not found
     */
    public function validateStepTransition(string $problemId, WorkflowStep $targetStep): bool
    {
        $problem = $this->problemService->getProblem($problemId);
        $currentStep = $this->getWorkflowStepFromNumber($problem->getCurrentStep());

        // Can't move to the same step
        if ($currentStep === $targetStep) {
            return false;
        }

        // Can only move one step forward or backward
        if ($targetStep === $currentStep->next() || $targetStep === $currentStep->previous()) {
            return true;
        }

        return false;
    }

    /**
     * Convert a numeric step to a WorkflowStep enum value.
     *
     * @param int $stepNumber Numeric step
     * @return WorkflowStep The corresponding step enum
     * @throws \InvalidArgumentException If step number is invalid
     */
    private function getWorkflowStepFromNumber(int $stepNumber): WorkflowStep
    {
        return match($stepNumber) {
            1 => WorkflowStep::ANALYZE,
            2 => WorkflowStep::BRAINSTORMING,
            3 => WorkflowStep::PLANNING,
            4 => WorkflowStep::IMPLEMENTATION,
            default => throw new \InvalidArgumentException("Invalid step number: {$stepNumber}"),
        };
    }

    /**
     * Convert a WorkflowStep enum value to a numeric step.
     *
     * @param WorkflowStep $step The step enum
     * @return int The corresponding numeric step
     */
    private function getNumberFromWorkflowStep(WorkflowStep $step): int
    {
        return match($step) {
            WorkflowStep::ANALYZE => 1,
            WorkflowStep::BRAINSTORMING => 2,
            WorkflowStep::PLANNING => 3,
            WorkflowStep::IMPLEMENTATION => 4,
        };
    }
}
