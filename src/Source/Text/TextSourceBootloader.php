<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\Source\Text;

use Butschster\ContextGenerator\Application\Bootloader\SourceFetcherBootloader;
use Butschster\ContextGenerator\Source\Registry\SourceRegistryInterface;
use Spiral\Boot\Bootloader\Bootloader;

final class TextSourceBootloader extends Bootloader
{
    #[\Override]
    public function defineSingletons(): array
    {
        return [
            TextSourceFetcher::class => TextSourceFetcher::class,
        ];
    }

    public function init(
        SourceFetcherBootloader $registry,
        SourceRegistryInterface $sourceRegistry,
        TextSourceFactory $factory,
    ): void {
        $registry->register(TextSourceFetcher::class);
        $sourceRegistry->register($factory);
    }
}
