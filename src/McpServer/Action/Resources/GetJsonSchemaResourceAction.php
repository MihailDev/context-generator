<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Resources;

use Butschster\ContextGenerator\Directories;
use Butschster\ContextGenerator\FilesInterface;
use Butschster\ContextGenerator\McpServer\Routing\Attribute\Get;
use Mcp\Types\ReadResourceResult;
use Mcp\Types\TextResourceContents;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class GetJsonSchemaResourceAction
{
    public function __construct(
        private LoggerInterface $logger,
        private FilesInterface $files,
        private Directories $dirs,
    ) {}

    #[Get(path: '/resource/ctx/json-schema', name: 'resources.ctx.json-schema')]
    public function __invoke(ServerRequestInterface $request): ReadResourceResult
    {
        $this->logger->info('Getting JSON schema');

        return new ReadResourceResult([
            new TextResourceContents(
                text: $this->getJsonSchema(),
                uri: 'ctx://json-schema',
                mimeType: 'application/json',
            ),
        ]);
    }

    /**
     * Get simplified JSON schema
     */
    private function getJsonSchema(): string
    {
        $schema = \json_decode(
            $this->files->read($this->dirs->jsonSchemaPath),
            associative: true,
        );

        unset(
            $schema['properties']['import'],
            $schema['properties']['settings'],
            $schema['definitions']['document']['properties']['modifiers'],
            $schema['definitions']['source']['properties']['modifiers'],
            $schema['definitions']['urlSource'],
            $schema['definitions']['githubSource'],
            $schema['definitions']['textSource'],
            $schema['definitions']['composerSource'],
            $schema['definitions']['php-content-filter'],
            $schema['definitions']['php-docs'],
            $schema['definitions']['sanitizer'],
            $schema['definitions']['modifiers'],
            $schema['definitions']['visibilityOptions'],
        );

        $schema['definitions']['source']['properties']['type']['enum'] = ['file', 'tree', 'git_diff'];

        return (string) \json_encode($schema);
    }
}
