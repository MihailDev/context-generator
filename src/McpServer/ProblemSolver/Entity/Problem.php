<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Entity;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Enum\WorkflowStep;

/**
 * Represents a problem to be solved.
 */
class Problem
{
    /**
     * @var string Unique identifier for the problem
     */
    private string $id;

    /**
     * @var string Original description of the problem
     */
    private string $originalProblem;

    /**
     * @var string|null Type of the problem (feature, bug, research, refactoring)
     */
    private ?string $type = null;

    /**
     * @var string|null Default project related to the problem
     */
    private ?string $defaultProject = null;

    /**
     * @var string|null Draft guide for brainstorming
     */
    private ?string $brainstormingDraft = null;

    /**
     * @var array<string, mixed> Problem context with related information
     */
    private array $context = [];

    private WorkflowStep $currentStep = WorkflowStep::NEW;

    public function __construct(string $id, string $originalProblem)
    {
        $this->id = $id;
        $this->originalProblem = $originalProblem;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOriginalProblem(): string
    {
        return $this->originalProblem;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDefaultProject(): ?string
    {
        return $this->defaultProject;
    }

    public function setDefaultProject(string $defaultProject): self
    {
        $this->defaultProject = $defaultProject;
        return $this;
    }

    public function getBrainstormingDraft(): ?string
    {
        return $this->brainstormingDraft;
    }

    public function setBrainstormingDraft(string $brainstormingDraft): self
    {
        $this->brainstormingDraft = $brainstormingDraft;
        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function getCurrentStep(): WorkflowStep
    {
        return $this->currentStep;
    }

    public function setCurrentStep(WorkflowStep $currentStep): self
    {
        $this->currentStep = $currentStep;
        return $this;
    }

    public function addContextItem(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Convert the problem to an array representation.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'originalProblem' => $this->originalProblem,
            'type' => $this->type,
            'defaultProject' => $this->defaultProject,
            'brainstormingDraft' => $this->brainstormingDraft,
            'context' => $this->context,
            'currentStep' => $this->currentStep->value,
        ];
    }

    /**
     * Create a Problem instance from an array.
     */
    public static function fromArray(array $data): self
    {
        $problem = new self($data['id'], $data['originalProblem']);

        if (isset($data['type'])) {
            $problem->setType($data['type']);
        }

        if (isset($data['defaultProject'])) {
            $problem->setDefaultProject($data['defaultProject']);
        }

        if (isset($data['brainstormingDraft'])) {
            $problem->setBrainstormingDraft($data['brainstormingDraft']);
        }

        if (isset($data['context'])) {
            $problem->setContext($data['context']);
        }

        if (isset($data['currentStep'])) {
            $problem->setCurrentStep(WorkflowStep::tryFrom($data['currentStep']) ?? WorkflowStep::NEW);;
        }

        return $problem;
    }
}
