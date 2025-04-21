<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Data\InstructionContent;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\ProblemInstruction;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\WorkflowStep;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Mcp\Types\TextContent;

/**
 * Service for generating step-specific instructions for problem solving.
 */
final readonly class InstructionService
{
    public function __construct(
        private InstructionContent $instructionTemplates,
    ) {}

    /**
     * Get instructions for the first analysis phase.
     *
     * @param Problem $problem The problem being analyzed
     * @return TextContent Instructions for first analysis
     */
    public function getFirstAnalyzeInstructions(Problem $problem): TextContent
    {
        return new TextContent(
            $this->getInstruction(ProblemInstruction::FirstAnalyzeInstruction),
        );
    }

    /**
     * Get analyze step instructions for continuing or restoring.
     *
     * @param Problem $problem The problem being analyzed
     * @return TextContent Instructions for analysis
     */
    public function getAnalyzeInstructions(Problem $problem): TextContent
    {
        return new TextContent(
            $this->getInstruction(ProblemInstruction::AnalyzeInstruction),
        );
    }

    /**
     * Get pause instructions after saving the brainstorming draft.
     *
     * @param Problem $problem The current problem
     * @return TextContent Pause instructions
     */
    public function getPauseInstructions(Problem $problem): TextContent
    {
        return new TextContent(
            $this->getInstruction(
                ProblemInstruction::PauseInstructions,
                ['{problem_id}' => $problem->getId()],
            ),
        );
    }

    /**
     * Get instructions for completing the analysis step.
     *
     * @param Problem $problem The current problem
     * @return TextContent Complete instructions
     */
    public function getAnalyzeCompleteInstructions(Problem $problem): TextContent
    {
        return new TextContent(
            $this->getInstruction(
                ProblemInstruction::AnalyzeCompleteInstructions,
                [
                    '{problem_id}' => $problem->getId(),
                    '{problem_type}' => $problem->getType(),
                    '{default_project}' => $problem->getDefaultProject(),
                ],
            ),
        );
    }

    /**
     * Get instructions for continuing after an error.
     *
     * @param Problem $problem The current problem
     * @param string $error The error message
     * @return TextContent Error recovery instructions
     */
    public function getContinueInstructionsOnError(
        Problem $problem,
        string $error,
    ): TextContent {
        return new TextContent(
            $this->getInstruction(
                ProblemInstruction::ContinueInstructionsOnError,
                [
                    '{problem_id}' => $problem->getId(),
                    '{error_message}' => $error,
                    '{current_step}' => $problem->getCurrentStep()->value,
                ],
            ),
        );
    }

    /**
     * Get instructions for continuing a problem.
     *
     * @param Problem $problem The problem to continue
     * @return TextContent Continue instructions
     */
    public function getContinueInstruction(Problem $problem): TextContent
    {
        $currentStep = $problem->getCurrentStep();
        $problemId = $problem->getId();

        $returnReason = $problem->getReturnReason();
        $returnReasonText = $returnReason !== null
            ? "\n\n**Return Reason:** {$returnReason}\n"
            : '';

        $contextFormatted = $this->formatProblemContext($problem->getContext());

        $brainstormingDraftText = '';
        if ($problem->getBrainstormingDraft() !== null) {
            $brainstormingDraftText = <<<TEXT

**Brainstorming Draft:**
{$problem->getBrainstormingDraft()}

TEXT;
        }

        // Add step-specific instructions
        $stepInstructions = $this->getStepInstructions($problem, $currentStep);

        return new TextContent(
            $this->getInstruction(
                ProblemInstruction::ContinueInstructions,
                [
                    '{problem_id}' => $problemId,
                    '{current_step}' => $currentStep->value,
                    '{problem_type}' => $problem->getType(),
                    '{default_project}' => $problem->getDefaultProject(),
                    '{return_reason_text}' => $returnReasonText,
                    '{original_problem}' => $problem->getOriginalProblem(),
                    '{brainstorming_draft_text}' => $brainstormingDraftText,
                    '{context_formatted}' => $contextFormatted,
                    '{step_instructions}' => $stepInstructions,
                ],
            ),
        );
    }

    /**
     * Format problem context for display.
     *
     * @param array<string, mixed> $context The problem context
     * @return string Formatted context
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
     * Get step-specific instructions for a problem.
     *
     * @param Problem $problem The problem
     * @param WorkflowStep $step The workflow step
     * @return string Step-specific instructions
     */
    public function getStepInstructions(
        Problem $problem,
        WorkflowStep $step,
    ): string {
        $instructionType = match ($step) {
            WorkflowStep::ANALYZE => ProblemInstruction::AnalyzeInstruction,
            WorkflowStep::BRAINSTORMING => ProblemInstruction::BrainstormingInstructions,
            WorkflowStep::PLANNING => ProblemInstruction::TaskPlanInstructions,
            WorkflowStep::IMPLEMENTATION => ProblemInstruction::SolveTaskInstructions,
        };

        return $this->getInstruction($instructionType);
    }

    /**
     * Get instruction by template with replacements.
     *
     * @param ProblemInstruction $template The instruction template
     * @param array<string, string> $replaces Key-value pairs for replacements
     * @return string Processed instruction content
     */
    private function getInstruction(ProblemInstruction $template, array $replaces = []): string
    {
        $content = $this->instructionTemplates->getInstructionContent($template);

        if (empty($replaces)) {
            return $content;
        }

        return \str_replace(
            \array_keys($replaces),
            \array_values($replaces),
            $content,
        );
    }
}
