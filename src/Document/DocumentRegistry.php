<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\Document;

use Butschster\ContextGenerator\Config\Registry\RegistryInterface;

/**
 * @implements RegistryInterface<Document>
 */
final class DocumentRegistry implements RegistryInterface
{
    public function __construct(
        /** @var array<Document> */
        private array $documents = [],
    ) {}

    public function getType(): string
    {
        return 'documents';
    }

    /**
     * Register a document in the registry
     */
    public function register(Document $document): self
    {
        $this->documents[] = $document;

        return $this;
    }

    public function getItems(): array
    {
        return $this->documents;
    }

    public function jsonSerialize(): array
    {
        return $this->documents;
    }
}
