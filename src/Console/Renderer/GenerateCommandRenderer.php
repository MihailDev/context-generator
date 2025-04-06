<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\Console\Renderer;

use Butschster\ContextGenerator\Document\Compiler\CompiledDocument;
use Butschster\ContextGenerator\Document\Document;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Renderer for the generate command output
 */
final readonly class GenerateCommandRenderer
{
    /**
     * Maximum line width for consistent display
     */
    private const int MAX_LINE_WIDTH = 120;

    /**
     * Success indicator symbol
     */
    private const string SUCCESS_SYMBOL = '✓';

    /**
     * Warning indicator symbol
     */
    private const string WARNING_SYMBOL = '!';

    /**
     * Error indicator symbol
     */
    private const string ERROR_SYMBOL = '✗';

    public function __construct(
        private OutputInterface $output,
    ) {}

    /**
     * Render the compilation result for a document
     */
    public function renderCompilationResult(Document $document, CompiledDocument $compiledDocument): void
    {
        \assert($this->output instanceof SymfonyStyle);

        $hasErrors = $compiledDocument->errors->hasErrors();
        $description = $document->description;
        $outputPath = $document->outputPath;

        // Calculate padding to align the document descriptions
        $padding = $this->calculatePadding($description, $outputPath);

        if ($hasErrors) {
            // Render warning line with document info
            $this->output->writeln(
                \sprintf(
                    ' <fg=yellow>%s</> %s <fg=yellow>[%s]</><fg=gray>%s</>',
                    $this->padRight(self::WARNING_SYMBOL, 1),
                    $description,
                    $outputPath,
                    $padding,
                ),
            );

            // Render errors
            foreach ($compiledDocument->errors as $error) {
                $this->output->writeln(\sprintf('    <fg=red>%s</> %s', self::ERROR_SYMBOL, $error));
            }

            $this->output->newLine();
        } else {
            // Render success line with document info
            $this->output->writeln(
                \sprintf(
                    ' <fg=green>%s</> %s <fg=cyan>[%s]</><fg=gray>%s</>',
                    $this->padRight(self::SUCCESS_SYMBOL, 2),
                    $description,
                    $outputPath,
                    $padding,
                ),
            );
        }
    }

    /**
     * Calculate padding to align the document information
     */
    private function calculatePadding(string $description, string $outputPath): string
    {
        $totalLength = \strlen($description) + \strlen($outputPath) + 5; // 5 accounts for spaces and brackets
        $padding = \max(0, self::MAX_LINE_WIDTH - $totalLength);

        return \str_repeat('.', $padding);
    }

    /**
     * Pad a string on the right with spaces
     */
    private function padRight(string $text, int $length): string
    {
        return \str_pad($text, $length, ' ', \STR_PAD_RIGHT);
    }
}
