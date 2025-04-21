<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Mcp\Types\TextContent;
use Psr\Log\LoggerInterface;

/**
 * Handler for the Plan step in problem solving.
 * Handles task planning and organizing changes for implementation.
 */
final readonly class PlanHandler implements StepHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ProblemService $problemService,
        private InstructionService $instructionService,
    ) {}

    /**
     * Add or modify a change description for a task
     *
     * @param Problem $problem Problem entity
     * @param string $taskNumber Task number
     * @param string $filePath Path to the file being changed
     * @param string $changeType Type of change (new, change, delete)
     * @param string $goal Goal of the change
     * @param string $changeDescription Description of the change
     * @param array $context Additional context for the change
     *
     * @return TextContent Instructions for the next steps
     */
    public function addOrModifyTaskChangeDescription(
        Problem $problem,
        string $taskNumber,
        string $filePath,
        string $changeType,
        string $goal,
        string $changeDescription,
        array $context = [],
    ): TextContent {
        $this->logger->info('Adding or modifying task change description', [
            'problem_id' => $problem->id,
            'task_number' => $taskNumber,
            'file_path' => $filePath,
            'change_type' => $changeType,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::PLAN);

        // Update problem context with the new change information
        $problemContext = $problem->getContext();

        // Make sure the task exists
        if (!isset($problemContext['tasks'][$taskNumber])) {
            throw new \InvalidArgumentException("Task {$taskNumber} does not exist");
        }

        // Initialize changes array if it doesn't exist
        $problemContext['tasks'][$taskNumber]['changes'] = $problemContext['tasks'][$taskNumber]['changes'] ?? [];

        // Add or update the change
        $problemContext['tasks'][$taskNumber]['changes'][$filePath] = [
            'change_type' => $changeType,
            'goal' => $goal,
            'description' => $changeDescription,
            'context' => $context,
            'approved' => false,
        ];

        // Update the problem with the new context
        $this->problemService->updateProblemContext($problem, $problemContext);

        return $this->instructionService->getContinueInstruction($problem);
    }

    /**
     * Remove a task change description
     *
     * @param Problem $problem Problem entity
     * @param string $taskNumber Task number
     * @param string $filePath Path to the file being changed
     *
     * @return TextContent Instructions for the next steps
     */
    public function removeTaskChangeDescription(
        Problem $problem,
        string $taskNumber,
        string $filePath,
    ): TextContent {
        $this->logger->info('Removing task change description', [
            'problem_id' => $problem->id,
            'task_number' => $taskNumber,
            'file_path' => $filePath,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::PLAN);

        // Update problem context to remove the change
        $problemContext = $problem->getContext();

        // Make sure the task exists
        if (!isset($problemContext['tasks'][$taskNumber])) {
            throw new \InvalidArgumentException("Task {$taskNumber} does not exist");
        }

        // Make sure the changes array exists
        if (isset($problemContext['tasks'][$taskNumber]['changes'][$filePath])) {
            unset($problemContext['tasks'][$taskNumber]['changes'][$filePath]);
            $this->problemService->updateProblemContext($problem, $problemContext);
        }

        return $this->instructionService->getContinueInstruction($problem);
    }

    /**
     * Get an overview of task changes
     *
     * @param Problem $problem Problem entity
     * @param string $taskNumber Task number
     *
     * @return array Task description and changes
     */
    public function getTaskChangesOverview(Problem $problem, string $taskNumber): array
    {
        $this->logger->info('Getting task changes overview', [
            'problem_id' => $problem->id,
            'task_number' => $taskNumber,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::PLAN);

        $problemContext = $problem->getContext();

        // Make sure the task exists
        if (!isset($problemContext['tasks'][$taskNumber])) {
            throw new \InvalidArgumentException("Task {$taskNumber} does not exist");
        }

        $task = $problemContext['tasks'][$taskNumber];

        // Prepare the overview
        $changesOverview = [];
        if (isset($task['changes']) && \is_array($task['changes'])) {
            foreach ($task['changes'] as $filePath => $change) {
                $changesOverview[] = [
                    'filePath' => $filePath,
                    'change_type' => $change['change_type'],
                    'goal' => $change['goal'],
                    'approved' => $change['approved'] ?? false,
                ];
            }
        }

        return [
            'task' => [
                'number' => $taskNumber,
                'title' => $task['title'] ?? "Task {$taskNumber}",
                'description' => $task['description'] ?? '',
                'project_name' => $task['project_name'] ?? '',
                'developer' => $task['project_developer_id'] ?? '',
            ],
            'changes' => $changesOverview,
        ];
    }

    /**
     * Approve task changes
     *
     * @param Problem $problem Problem entity
     * @param string $taskNumber Task number
     *
     * @return TextContent Instructions for the next steps
     */
    public function approveTaskChanges(Problem $problem, string $taskNumber): TextContent
    {
        $this->logger->info('Approving task changes', [
            'problem_id' => $problem->id,
            'task_number' => $taskNumber,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::PLAN);

        // Update problem context to mark all changes in the task as approved
        $problemContext = $problem->getContext();

        // Make sure the task exists
        if (!isset($problemContext['tasks'][$taskNumber])) {
            throw new \InvalidArgumentException("Task {$taskNumber} does not exist");
        }

        // Make sure the changes array exists and mark all changes as approved
        if (isset($problemContext['tasks'][$taskNumber]['changes']) && \is_array($problemContext['tasks'][$taskNumber]['changes'])) {
            foreach ($problemContext['tasks'][$taskNumber]['changes'] as $filePath => $change) {
                $problemContext['tasks'][$taskNumber]['changes'][$filePath]['approved'] = true;
            }
            $this->problemService->updateProblemContext($problem, $problemContext);
        }

        // Check if all changes in all tasks are approved
        $allApproved = true;
        $hasChanges = false;

        foreach ($problemContext['tasks'] as $task) {
            if (isset($task['changes']) && \is_array($task['changes'])) {
                foreach ($task['changes'] as $change) {
                    $hasChanges = true;
                    if (!($change['approved'] ?? false)) {
                        $allApproved = false;
                        break 2;
                    }
                }
            }
        }

        // If all changes are approved and there are changes, move to the next step
        if ($allApproved && $hasChanges) {
            $this->problemService->startSolveStep($problem);
            return $this->instructionService->getPauseInstructions($problem);
        }

        return $this->instructionService->getContinueInstruction($problem);
    }

    /**
     * Return to plan step from a later step
     *
     * @param Problem $problem Problem entity
     * @param string $returnReason Reason for returning to this step
     *
     * @return TextContent Instructions for the next steps
     */
    public function returnToStep(Problem $problem, string $returnReason): TextContent
    {
        $this->logger->info('Returning to plan step', [
            'problem_id' => $problem->id,
            'return_reason' => $returnReason,
        ]);

        $this->problemService->restoreToStep(
            $problem,
            ProblemStep::PLAN,
            $returnReason,
        );

        return $this->instructionService->getContinueInstruction($problem);
    }
}
