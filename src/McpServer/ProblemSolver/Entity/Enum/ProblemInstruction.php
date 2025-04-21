<?php

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum;

enum ProblemInstruction: string
{
    case FirstAnalyzeInstruction = 'FirstAnalyzeInstruction';
    const AnalyzeInstruction = 'AnalyzeInstruction';
}
