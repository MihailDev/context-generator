<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services\Handlers;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\InstructionService;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Services\ProblemService;
use Butschster\ContextGenerator\DirectoriesInterface;
use Mcp\Types\TextContent;
use Psr\Log\LoggerInterface;
use Spiral\Files\FilesInterface;

/**
 * Handler for the Changes step in problem solving.
 * Handles actual implementation of changes to solve the problem.
 */
final readonly class ChangesHandler implements StepHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ProblemService $problemService,
        private InstructionService $instructionService,
        private FilesInterface $files,
        private DirectoriesInterface $dirs,
    ) {}

    /**
     * Get the next change to be implemented
     *
     * @param Problem $problem Problem entity
     *
     * @return array|null Next change to be implemented or null if no more changes
     */
    public function getNextChange(Problem $problem): ?array
    {
        $this->logger->info('Getting next change', [
            'problem_id' => $problem->id,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::CHANGES);

        $problemContext = $problem->getContext();

        // Track which changes have been implemented
        $problemContext['implemented_changes'] = $problemContext['implemented_changes'] ?? [];

        // Find the next unapproved change
        foreach ($problemContext['tasks'] as $taskNumber => $task) {
            if (isset($task['changes']) && \is_array($task['changes'])) {
                foreach ($task['changes'] as $filePath => $change) {
                    // Skip already implemented changes
                    $changeId = "{$taskNumber}_{$filePath}";
                    if (\in_array($changeId, $problemContext['implemented_changes'])) {
                        continue;
                    }

                    // Return the next change to be implemented
                    return [
                        'task_number' => $taskNumber,
                        'file_path' => $filePath,
                        'change_type' => $change['change_type'],
                        'goal' => $change['goal'],
                        'description' => $change['description'],
                        'context' => $change['context'] ?? [],
                    ];
                }
            }
        }

        // No more changes to implement
        return null;
    }

    /**
     * Make a change to implement a solution
     *
     * @param Problem $problem Problem entity
     * @param string $taskNumber Task number
     * @param string $filePath Path to the file being changed
     * @param string $content New content for the file
     *
     * @return TextContent Instructions for the next steps
     */
    public function makeChange(
        Problem $problem,
        string $taskNumber,
        string $filePath,
        string $content,
    ): TextContent {
        $this->logger->info('Making change', [
            'problem_id' => $problem->id,
            'task_number' => $taskNumber,
            'file_path' => $filePath,
        ]);

        // Validate we're in the correct step
        $this->problemService->checkStep($problem, ProblemStep::CHANGES);

        $problemContext = $problem->getContext();

        // Make sure the task exists
        if (!isset($problemContext['tasks'][$taskNumber])) {
            throw new \InvalidArgumentException("Task {$taskNumber} does not exist");
        }

        // Make sure the change exists
        if (!isset($problemContext['tasks'][$taskNumber]['changes'][$filePath])) {
            throw new \InvalidArgumentException("Change for file {$filePath} in task {$taskNumber} does not exist");
        }

        $change = $problemContext['tasks'][$taskNumber]['changes'][$filePath];
        $changeType = $change['change_type'];

        // Get the full file path
        $fullPath = (string) $this->dirs->getRootPath()->join($filePath);

        // Implement the change based on the change type
        switch ($changeType) {
            case 'new':
                // Create directory if it doesn't exist
                $directory = \dirname($fullPath);
                if (!$this->files->exists($directory)) {
                    $this->files->createDirectory($directory);
                }

                // Write the new file
                $this->files->write($fullPath, $content);
                break;

            case 'change':
                // Update the existing file
                if (!$this->files->exists($fullPath)) {
                    throw new \RuntimeException("File {$filePath} does not exist and cannot be changed");
                }

                $this->files->write($fullPath, $content);
                break;

            case 'delete':
                // Delete the file
                if (!$this->files->exists($fullPath)) {
                    throw new \RuntimeException("File {$filePath} does not exist and cannot be deleted");
                }

                $this->files->delete($fullPath);
                break;

            default:
                throw new \InvalidArgumentException("Invalid change type: {$changeType}");
        }

        // Mark the change as implemented
        $problemContext['implemented_changes'] = $problemContext['implemented_changes'] ?? [];
        $changeId = "{$taskNumber}_{$filePath}";
        $problemContext['implemented_changes'][] = $changeId;
        $this->problemService->updateProblemContext($problem, $problemContext);

        // Check if there are more changes to implement
        $nextChange = $this->getNextChange($problem);

        if ($nextChange === null) {
            // All changes have been implemented, complete the problem
            $this->problemService->completeProblem($problem);
            return $this->instructionService->getPauseInstructions($problem);
        }

        // Return instructions for the next change
        return $this->instructionService->getSolveTaskInstructions($problem);
    }

    /**
     * Return to changes step from a later step
     *
     * @param Problem $problem Problem entity
     * @param string $returnReason Reason for returning to this step
     *
     * @return TextContent Instructions for the next steps
     */
    public function returnToStep(Problem $problem, string $returnReason): TextContent
    {
        $this->logger->info('Returning to changes step', [
            'problem_id' => $problem->id,
            'return_reason' => $returnReason,
        ]);

        $this->problemService->restoreToStep(
            $problem,
            ProblemStep::CHANGES,
            $returnReason,
        );

        return $this->instructionService->getContinueInstruction($problem);
    }
}
