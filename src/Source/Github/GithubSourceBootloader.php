<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\Source\Github;

use Butschster\ContextGenerator\Application\Bootloader\GithubClientBootloader;
use Butschster\ContextGenerator\Application\Bootloader\SourceFetcherBootloader;
use Butschster\ContextGenerator\Source\Registry\SourceRegistryInterface;
use Spiral\Boot\Bootloader\Bootloader;

final class GithubSourceBootloader extends Bootloader
{
    #[\Override]
    public function defineDependencies(): array
    {
        return [GithubClientBootloader::class];
    }

    #[\Override]
    public function defineSingletons(): array
    {
        return [
            GithubSourceFetcher::class => GithubSourceFetcher::class,
        ];
    }

    public function init(
        SourceFetcherBootloader $registry,
        SourceRegistryInterface $sourceRegistry,
        GithubSourceFactory $factory,
    ): void {
        $registry->register(GithubSourceFetcher::class);
        $sourceRegistry->register($factory);
    }
}
