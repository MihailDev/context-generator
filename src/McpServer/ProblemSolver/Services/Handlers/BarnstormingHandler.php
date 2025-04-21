<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Butschster\ContextGenerator\McpServer\ProjectService\ProjectServiceInterface;
use Mcp\Types\TextContent;
use Psr\Log\LoggerInterface;

/**
 * Handler for the Barnstorming step in problem solving.
 * Handles the brainstorming and task creation phase.
 */
final readonly class BarnstormingHandler implements StepHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ProblemService $problemService,
        private InstructionService $instructionService,
        private ProjectServiceInterface $projectService,
    ) {}

    /**
     * Get developers for a specific project
     *
     * @param string $projectName Name of the project
     *
     * @return array List of developers with their IDs
     */
    public function getProjectDevelopers(string $projectName): array
    {
        $this->logger->info('Getting project developers', [
            'project_name' => $projectName,
        ]);

        // Here we would typically fetch developers from a repository
        // For now, we'll return placeholder data
        return [
            [
                'id' => 'dev1',
                'name' => 'Developer 1',
            ],
            [
                'id' => 'dev2',
                'name' => 'Developer 2',
            ],
        ];
    }

    /**
     * Get the list of tasks for a problem
     *
     * @param Problem $problem Problem entity
     *
     * @return array List of task titles with task numbers
     */
    public function getTaskList(Problem $problem): array
    {
        $this->logger->info('Getting task list', [
            'problem_id' => $problem->id,
        ]);

        // This would typically fetch tasks from a repository
        // For now, we'll assume tasks are stored in the problem context
        $taskList = [];
        $problemContext = $problem->getContext();

        if (isset($problemContext['tasks']) && \is_array($problemContext['tasks'])) {
            foreach ($problemContext['tasks'] as $taskNumber => $task) {
                $taskList[] = [
                    'number' => $taskNumber,
                    'title' => $task['title'] ?? "Task {$taskNumber}",
                ];
            }
        }

        return $taskList;
    }

    /**
     * Add or modify a task for the problem
     *
     * @param Problem $problem Problem entity
     * @param string $taskNumber Task number
     * @param string $projectName Project name
     * @param string $projectDeveloperId Project developer ID
     * @param string $title Task title
     * @param string $description Task description
     * @param array $context Task context
     *
     * @return TextContent Instructions for the next steps
     */
    public function addOrModifyTask(
        Problem $problem,
        string $taskNumber,
        string $projectName,
        string $projectDeveloperId,
        string $title,
        string $description,
        array $context = [],
    ): TextContent {
        $this->logger->info('Adding or modifying task', [
            'problem_id' => $problem->id,
            'task_number' => $taskNumber,
            'project_name' => $projectName,
            'title' => $title,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::BARNSTORMING);

        // Update problem context with the new task information
        $problemContext = $problem->getContext();
        $problemContext['tasks'] = $problemContext['tasks'] ?? [];
        $problemContext['tasks'][$taskNumber] = [
            'project_name' => $projectName,
            'project_developer_id' => $projectDeveloperId,
            'title' => $title,
            'description' => $description,
            'context' => $context,
            'approved' => false,
        ];

        // Update the problem with the new context
        $this->problemService->updateProblemContext($problem, $problemContext);

        return $this->instructionService->getContinueInstruction($problem);
    }

    /**
     * Delete a task for the problem
     *
     * @param Problem $problem Problem entity
     * @param string $taskNumber Task number
     *
     * @return TextContent Instructions for the next steps
     */
    public function deleteTask(Problem $problem, string $taskNumber): TextContent
    {
        $this->logger->info('Deleting task', [
            'problem_id' => $problem->id,
            'task_number' => $taskNumber,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::BARNSTORMING);

        // Update problem context to remove the task
        $problemContext = $problem->getContext();
        if (isset($problemContext['tasks'][$taskNumber])) {
            unset($problemContext['tasks'][$taskNumber]);
            $this->problemService->updateProblemContext($problem, $problemContext);
        }

        return $this->instructionService->getContinueInstruction($problem);
    }

    /**
     * Approve a task for the problem
     *
     * @param Problem $problem Problem entity
     * @param string $taskNumber Task number
     *
     * @return TextContent Instructions for the next steps
     */
    public function approveTask(Problem $problem, string $taskNumber): TextContent
    {
        $this->logger->info('Approving task', [
            'problem_id' => $problem->id,
            'task_number' => $taskNumber,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::BARNSTORMING);

        // Update problem context to mark the task as approved
        $problemContext = $problem->getContext();
        if (isset($problemContext['tasks'][$taskNumber])) {
            $problemContext['tasks'][$taskNumber]['approved'] = true;
            $this->problemService->updateProblemContext($problem, $problemContext);
        }

        // Check if all tasks are approved
        $allApproved = true;
        foreach ($problemContext['tasks'] as $task) {
            if (!($task['approved'] ?? false)) {
                $allApproved = false;
                break;
            }
        }

        // If all tasks are approved, move to the next step
        if ($allApproved && \count($problemContext['tasks']) > 0) {
            $this->problemService->startPlanStep($problem);
            return $this->instructionService->getPauseInstructions($problem);
        }

        return $this->instructionService->getContinueInstruction($problem);
    }

    /**
     * Return to barnstorming step from a later step
     *
     * @param Problem $problem Problem entity
     * @param string $returnReason Reason for returning to this step
     *
     * @return TextContent Instructions for the next steps
     */
    public function returnToStep(Problem $problem, string $returnReason): TextContent
    {
        $this->logger->info('Returning to barnstorming step', [
            'problem_id' => $problem->id,
            'return_reason' => $returnReason,
        ]);

        $this->problemService->restoreToStep(
            $problem,
            ProblemStep::BARNSTORMING,
            $returnReason,
        );

        return $this->instructionService->getContinueInstruction($problem);
    }
}
