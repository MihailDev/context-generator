<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\Fetcher\Url;

/**
 * Interface for HTML content cleaners
 */
interface HtmlCleanerInterface
{
    /**
     * Clean HTML content and extract meaningful text
     */
    public function clean(string $html): string;
}
