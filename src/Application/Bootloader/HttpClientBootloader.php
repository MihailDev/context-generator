<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\Application\Bootloader;

use Butschster\ContextGenerator\Lib\HttpClient\HttpClientFactory;
use Butschster\ContextGenerator\Lib\HttpClient\HttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Spiral\Boot\Bootloader\Bootloader;

final class HttpClientBootloader extends Bootloader
{
    #[\Override]
    public function defineSingletons(): array
    {
        return [
            HttpClientInterface::class => static fn(
                Client $httpClient,
                HttpFactory $httpMessageFactory,
            ) => HttpClientFactory::create(
                $httpClient,
                $httpMessageFactory,
            ),
        ];
    }
}
