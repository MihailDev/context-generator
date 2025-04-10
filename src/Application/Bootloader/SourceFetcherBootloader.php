<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\Application\Bootloader;

use Butschster\ContextGenerator\Source\Fetcher\SourceFetcherInterface;
use Butschster\ContextGenerator\Source\Fetcher\SourceFetcherProvider;
use Butschster\ContextGenerator\SourceParserInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Container;

#[Singleton]
final class SourceFetcherBootloader extends Bootloader
{
    /** @var class-string<SourceFetcherInterface>[] */
    private array $fetchers = [];

    /**
     * Register a source fetcher
     * @param class-string<SourceFetcherInterface> $fetcher
     */
    public function register(string $fetcher): self
    {
        $this->fetchers[] = $fetcher;

        return $this;
    }

    #[\Override]
    public function defineSingletons(): array
    {
        return [
            SourceParserInterface::class => static function (
                Container $container,
                SourceFetcherBootloader $bootloader,
            ) {
                $fetchers = $bootloader->getFetchers();
                return new SourceFetcherProvider(
                    fetchers: \array_map(
                        static fn(string $fetcher) => $container->get($fetcher),
                        $fetchers,
                    ),
                );
            },
        ];
    }

    public function getFetchers(): array
    {
        return $this->fetchers;
    }
}
