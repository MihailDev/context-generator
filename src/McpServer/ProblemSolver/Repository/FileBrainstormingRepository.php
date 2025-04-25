<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Repository;

use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Brainstorming;
use Butschster\ContextGenerator\McpServer\ProblemSolver\Entity\Problem;
use Spiral\Files\FilesInterface;

readonly class FileBrainstormingRepository implements BrainstormingRepositoryInterface
{
    public function __construct(
        private FileProblemRepository $problemRepository,
        private FilesInterface $files,
    ) {}

    public function save(Brainstorming $brainstorming): bool
    {
        $filePath = $this->getBrainstormingPath($brainstorming->problem_id);

        $content =  \json_encode($brainstorming->toArray(), JSON_PRETTY_PRINT);

        return $this->files->write($filePath, $content, FilesInterface::RUNTIME, true);
    }

    public function findById(Problem $problem): ?Brainstorming
    {
        $filePath = $this->getBrainstormingPath($problem->getId());

        if (!$this->files->exists($filePath)) {
            return null;
        }

        $content = $this->files->read($filePath);

        $data = \json_decode($content, true);

        return Brainstorming::fromArray($data);
    }

    public function exists(Problem $problem): bool
    {
        return $this->files->exists($this->getBrainstormingPath($problem->getId()));
    }

    private function getBrainstormingPath(
        string $problemId,
    ): string {
        return $this->problemRepository->getProblemDirectory($problemId) . DIRECTORY_SEPARATOR . 'brainstorming.json';
    }
}
