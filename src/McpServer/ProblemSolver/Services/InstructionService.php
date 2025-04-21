<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Data\InstructionContent;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Mcp\Types\TextContent;

/**
 * Service for generating step-specific instructions for problem solving.
 */
readonly class InstructionService
{
    public function __construct(
        private InstructionContent $instructionContent,
    ) {}

    /**
     * Get instructions for the first analysis phase.
     */
    public function getFirstAnalyzeInstructions(Problem $problem): TextContent
    {
        return new TextContent($this->instructionContent->getFirstAnalyzeInstruction());
    }

    /**
     * Get analyze step instructions for continuing or restoring.
     */
    public function getAnalyzeInstructions(Problem $problem): TextContent
    {
        return new TextContent($this->instructionContent->getAnalyzeInstruction());
    }

    /**
     * Get pause instructions after saving the brainstorming draft.
     */
    public function getPauseInstructions(Problem $problem): TextContent
    {
        return <<<INSTRUCTIONS
## Analysis Step Complete

You have successfully saved the brainstorming draft for this problem. Step 1 (Analysis) is now complete.

To continue to Step 2 (Brainstorming), use the ContinueOrRestoreAction with the problem ID.

If you need to pause now, you can resume later by using the same ContinueOrRestoreAction.

Remember the problem ID to continue working on this problem.
INSTRUCTIONS;
    }

    /**
     * Format problem context for display.
     */
    public function formatProblemContext(array $context): string
    {
        $formatted = "## Problem Context\n\n";

        // Format directories if available
        if (isset($context['directories']) && \is_array($context['directories'])) {
            $formatted .= "### Directories\n";
            foreach ($context['directories'] as $directory) {
                $formatted .= "- {$directory}\n";
            }
            $formatted .= "\n";
        }

        // Format files if available
        if (isset($context['files']) && \is_array($context['files'])) {
            $formatted .= "### Files\n";
            foreach ($context['files'] as $file) {
                $formatted .= "- {$file}\n";
            }
            $formatted .= "\n";
        }

        // Format packages if available
        if (isset($context['packages']) && \is_array($context['packages'])) {
            $formatted .= "### Packages\n";
            foreach ($context['packages'] as $package) {
                $formatted .= "- {$package}\n";
            }
            $formatted .= "\n";
        }

        // Format documents if available
        if (isset($context['documents']) && \is_string($context['documents'])) {
            $formatted .= "### Documents\n";
            $formatted .= $context['documents'] . "\n\n";
        }

        return $formatted;
    }

    /**
     * Get instructions for a specific step.
     *
     * @param int $step The step number
     * @param array $context Additional context for instructions
     */
    public function getStepInstructions(
        int   $step,
        array $context = [],
    ): string {
        return match ($step) {
            1 => $this->getAnalyzeInstructions(),
            2 => $this->getBrainstormingInstructions($context),
            3 => $this->getTaskPlanInstructions($context),
            4 => $this->getSolveTaskInstructions($context),
            default => "Instructions for step {$step} are not yet implemented.",
        };
    }

    public function getContinueInstructionsOnError(
        Problem $problem,
        string $error,
    ): TextContent {
        //todo: get instructions for continuing on error
    }

    public function getContinueInstruction(Problem $problem): TextContent
    {
        //todo: get instructions for continuing
        return new TextContent("Continue");
    }

    public function getAnalyzeCompleteInstructions(Problem $problem): TextContent
    {

    }

    /**
     * Get instructions for brainstorming step.
     */
    private function getBrainstormingInstructions(array $context = []): string
    {
        return <<<INSTRUCTIONS
## Brainstorming Step Instructions

In this step, you'll work with the team to brainstorm solutions to the problem:

1. **Identify participants needed**:
   - Project developers familiar with the implementation details
   - Business analyst for understanding requirements
   - Subject-matter expert to evaluate ideas
   - AI prompt engineer for clarity of instructions

2. **Conduct multi-round brainstorming**:
   - Each participant should consider multiple perspectives
   - Ask for any required information about the project
   - Present thoughts to all participants
   - Discuss ideas from other participants
   - Repeat if there are disagreements or unfinished conversations

3. **Collect and organize all insights from the discussion**

4. **Create tasks for each affected project**:
   - Include project name
   - Developer information
   - Short description

Once you've completed these steps, save your task list using the appropriate action.
INSTRUCTIONS;
    }

    /**
     * Get instructions for task planning step.
     */
    private function getTaskPlanInstructions(array $context = []): string
    {
        return <<<INSTRUCTIONS
## Task Plan Instructions

In this step, you'll create a detailed plan for each approved task:

1. **For each task, define**:
   - Project name
   - Developer information
   - Short description
   - List of changes:
     - File path
     - Change type (new, change, delete)
     - Description of updates
     - Required context (directories, files, etc.)

2. **Review and finalize the plan**:
   - Ensure all necessary changes are included
   - Verify completeness of context information
   - Confirm alignment with the original problem requirements

3. **Approve the task changes** when ready to proceed to implementation

Once all tasks are planned and approved, the system will pause until you're ready for implementation.
INSTRUCTIONS;
    }

    /**
     * Get instructions for task solving step.
     */
    private function getSolveTaskInstructions(array $context = []): string
    {
        return <<<INSTRUCTIONS
## Solve Task Instructions

In this step, you'll implement the changes defined in your task plan:

1. **For each change**:
   - Review the context and instructions
   - Implement the required modifications
   - Test the changes as appropriate
   - Document any challenges or deviations from the plan

2. **Submit each change** for review and integration

3. **Continue until all changes are complete**

The system will provide you with each change sequentially, along with the necessary context and specific instructions.
INSTRUCTIONS;
    }
}
