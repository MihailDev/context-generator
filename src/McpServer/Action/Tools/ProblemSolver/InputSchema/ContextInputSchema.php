<?php

declare(strict_types=1);

namespace Butschster\ContextGenerator\McpServer\Action\Tools\ProblemSolver\InputSchema;

class ContextInputSchema
{
    public const array PROPERTIES = [
        "directoryOverview" => [
            "description" => "List directories for overview",
            "type" => "array",
            "items" => [
                "type" => "object",
                "properties" => [
                    "src" => [
                        "type" => "string",
                        "description" => "Directory Path",
                    ],
                    "purpose" => [
                        "type" => "string",
                        "description" => "Purpose of the directory",
                    ],
                ],
                "required" => ["src", "purpose"],
            ],
        ],
        "vendorOverview" => [
            "description" => "Vendor Packages for overview",
            "type" => "array",
            "items" => [
                "type" => "object",
                "properties" => [
                    "package" => [
                        "type" => "string",
                        "description" => "Package Name",
                    ],
                    "purpose" => [
                        "type" => "string",
                        "description" => "Purpose of the package",
                    ],
                ],
                "required" => ["package", "purpose"],
            ],
        ],
        "fileSources" => [
            "description" => "List files for view source",
            "type" => "array",
            "items" => [
                "type" => "object",
                "properties" => [
                    "src" => [
                        "type" => "string",
                        "description" => "File Path",
                    ],
                    "purpose" => [
                        "type" => "string",
                        "description" => "Purpose of the file",
                    ],
                ],
                "required" => ["src", "purpose"],
            ],
        ],
        "notes" => [
            "description" => "List notes",
            "type" => "array",
            "items" => [
                "type" => "object",
                "properties" => [
                    "title" => [
                        "type" => "string",
                        "description" => "Note title",
                    ],
                    "content" => [
                        "type" => "string",
                        "description" => "Note content",
                    ],
                ],
                "required" => ["title", "content"],
            ],
        ],
    ];
}
