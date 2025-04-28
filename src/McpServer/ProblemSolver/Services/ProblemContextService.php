<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\ProblemSolver\Services;

use Butschster\ContextGenerator\Document\Compiler\DocumentCompiler;
use Butschster\ContextGenerator\Document\Compiler\Error\ErrorCollection;
use Butschster\ContextGenerator\Document\Document;
use Butschster\ContextGenerator\Lib\TreeBuilder\TreeViewConfig;
use Butschster\ContextGenerator\Source\File\FileSource;
use Butschster\ContextGenerator\Source\Tree\TreeSource;

final readonly class ProblemContextService
{
    public function __construct(
        private DocumentCompiler $documentCompiler,
    ) {}

    public function generate(array $context): string
    {
        $directoryOverview = $context['directoryOverview'] ?? [];
        $vendorOverview = $context['vendorOverview'] ?? [];
        $fileSources = $context['fileSources'] ?? [];
        $notes = $context['notes'] ?? [];

        $result = [
            $this->renderDirectoryOverview($directoryOverview),
            $this->renderVendorOverview($vendorOverview),
            $this->renderFileSources($fileSources),
            $this->renderNotes($notes),
        ];


        return \implode("\n", \array_filter($result));
    }

    private function renderDirectoryOverview(array $directoryOverview): ?string
    {
        $result = [];
        foreach ($directoryOverview as $directory) {

            $path = 'issue/' . \md5($directory['src']) . '.md';

            $document = Document::create(
                description: 'Directory: ' . $directory['src'],
                outputPath: $path,
                overwrite: true,
            );

            $source = new TreeSource(
                sourcePaths: $directory['src'],
                description: $directory['purpose'],
                filePattern: ['.php'],
                contains: [],
                notContains: [],
                treeView: new TreeViewConfig(
                    showSize: false,
                    showLastModified: false,
                    showCharCount: false,
                    includeFiles: true,
                    maxDepth: 0,
                    dirContext: [],
                ),
                tags: [],
            );

            $document->addSource($source);

            $result[] = (string) $this->documentCompiler->buildContent(new ErrorCollection(), $document)->content;
        }
        return $result ? \implode("\n", $result) : null;
    }

    private function renderVendorOverview(array $vendorOverview): ?string
    {
        $result = [];
        foreach ($vendorOverview as $directory) {
            //todo:
        }
        return $result ? \implode("\n", $result) : null;
    }

    private function renderFileSources(array $fileSources): ?string
    {
        if (empty($fileSources)) {
            return null;
        }
        $document = Document::create(
            description: 'File Sources',
            outputPath: 'issue/FileSources.md',
            overwrite: true,
        );

        foreach ($fileSources as $fileSource) {
            $source = new FileSource(
                sourcePaths: $fileSource['src'],
                description: $fileSource['purpose'],
                filePattern: ['.php'],
                contains: [],
                notContains: [],
                treeView: new TreeViewConfig(
                    enabled: false,
                ),
                tags: [],
            );

            $document->addSource($source);
        }


        return (string) $this->documentCompiler->buildContent(new ErrorCollection(), $document)->content;
    }

    private function renderNotes(array $notes): ?string
    {
        $result = [];
        foreach ($notes as $note) {
            $result[] = '## Note: ' . $note['title'] . "\n\n" . $note['content'] . "\n";
        }
        return $result ? \implode("\n", $result) : null;
    }
}
