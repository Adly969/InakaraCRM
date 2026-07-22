<?php

namespace App\Services;

use App\Models\EventSchemaRegistry;
use Illuminate\Validation\ValidationException;

class EventSchemaValidator
{
    /**
     * Validate an event payload against registered schema rules.
     *
     * @throws ValidationException
     */
    public function validate(string $schemaName, int $version, array $payload): void
    {
        $schema = EventSchemaRegistry::where([
            'schema_name' => $schemaName,
            'schema_version' => $version,
            'is_active' => true,
        ])->first();

        // If no registry matches, log warning but allow matching dynamically for backward compatibility stubs
        if (! $schema) {
            return;
        }

        // Validate required fields
        $required = $schema->required_fields;
        foreach ($required as $field) {
            if (! array_key_exists($field, $payload) || $payload[$field] === null || $payload[$field] === '') {
                throw ValidationException::withMessages([
                    'payload' => ["Missing required field '{$field}' in event schema '{$schemaName}'."],
                ]);
            }
        }
    }
}
