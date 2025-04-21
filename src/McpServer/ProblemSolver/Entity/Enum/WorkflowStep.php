<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum;

enum WorkflowStep: string
{
    case NEW = 'new';
    case ANALYZE = 'analyze';
    case BRAINSTORMING = 'brainstorming';
    case PLANNING = 'planning';
    case IMPLEMENTATION = 'implementation';

    /**
     * Get the next step in the workflow
     */
    public function next(): ?self
    {
        return match($this) {
            self::ANALYZE => self::BRAINSTORMING,
            self::BRAINSTORMING => self::PLANNING,
            self::PLANNING => self::IMPLEMENTATION,
            self::IMPLEMENTATION => null, // Final step
        };
    }

    /**
     * Get the previous step in the workflow
     */
    public function previous(): ?self
    {
        return match($this) {
            self::ANALYZE => null, // First step
            self::BRAINSTORMING => self::ANALYZE,
            self::PLANNING => self::BRAINSTORMING,
            self::IMPLEMENTATION => self::PLANNING,
        };
    }

    /**
     * Get all available steps as array
     */
    public static function toArray(): array
    {
        return [
            self::ANALYZE->value,
            self::BRAINSTORMING->value,
            self::PLANNING->value,
            self::IMPLEMENTATION->value,
        ];
    }

    /**
     * Check if this step is before another step
     */
    public function isBefore(self $step): bool
    {
        return match($this) {
            self::ANALYZE => $step !== self::ANALYZE,
            self::BRAINSTORMING => in_array($step, [self::PLANNING, self::IMPLEMENTATION]),
            self::PLANNING => $step === self::IMPLEMENTATION,
            self::IMPLEMENTATION => false,
        };
    }

    /**
     * Check if this step is after another step
     */
    public function isAfter(self $step): bool
    {
        return $step->isBefore($this);
    }

    /**
     * Create from string value
     */
    public static function fromString(string $value): ?self
    {
        return match($value) {
            self::ANALYZE->value => self::ANALYZE,
            self::BRAINSTORMING->value => self::BRAINSTORMING,
            self::PLANNING->value => self::PLANNING,
            self::IMPLEMENTATION->value => self::IMPLEMENTATION,
            default => null,
        };
    }
}
