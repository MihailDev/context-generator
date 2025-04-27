<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum;

/**
 * Enum representing different instruction types for problem solving steps.
 */
enum ProblemInstruction: string
{
    // Analyze step instructions
    case FirstAnalyzeInstruction = 'FirstAnalyzeInstruction';
    case AnalyzeInstruction = 'AnalyzeInstruction';
    case AnalyzeCompleteInstructions = 'AnalyzeCompleteInstructions';

    // Brainstorming step instructions
    case StartBrainstormingInstructions = 'StartBrainstormingInstructions';
    case BrainstormingInstructions = 'BrainstormingInstructions';

    // Task planning step instructions
    case TaskPlanInstructions = 'TaskPlanInstructions';

    // Implementation step instructions
    case SolveTaskInstructions = 'SolveTaskInstructions';

    // Common workflow instructions
    case PauseInstructions = 'PauseInstructions';
    case ContinueInstructions = 'ContinueInstructions';
    case ContinueInstructionsOnError = 'ContinueInstructionsOnError';
    case ProblemInfo = 'ProblemInfo';
}
