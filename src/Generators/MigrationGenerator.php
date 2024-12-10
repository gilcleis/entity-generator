<?php

namespace Gilcleis\Support\Generators;

use Carbon\Carbon;
use Exception;
use Gilcleis\Support\Events\SuccessCreateMessage;

class MigrationGenerator extends EntityGenerator
{
    public function generate(): void
    {
        $entities = $this->getTableName($this->model);

        $content = $this->getStub('migration', [
            'class' => $this->getPluralName($this->model),
            'entity' => $this->model,
            'entities' => $entities,
            'relations' => $this->relations,
            'fields' => $this->fields,
            'table' => $this->generateTable($this->fields)
        ]);
        $now = Carbon::now()->format('Y_m_d_His');

        $this->saveClass('migrations', "{$now}_{$entities}_create_table", $content);

        event(new SuccessCreateMessage("Created a new Migration: {$entities}_create_table"));
    }

    protected function isJson($typeName): bool
    {
        return $typeName == 'json';
    }

    protected function isRequired($typeName): bool
    {
        return !empty(explode('-', $typeName)[1]);
    }

    protected function isNullable($typeName): bool
    {
        return empty(explode('-', $typeName)[1]);
    }

    protected function getJsonLine($fieldName): string
    {
        if (env("DB_CONNECTION") == "mysql") {
            return "\$table->json('{$fieldName}')->nullable()->comment('{$fieldName}');";
        }

        return "\$table->jsonb('{$fieldName}')->default(\"{}\")->comment('{$fieldName}');";
    }

    protected function getRequiredLine($fieldName, $typeName): string
    {
        $type = explode('-', $typeName)[0];

        if ($type === 'timestamp' && env('DB_CONNECTION') === 'mysql') {
            return "\$table->{$type}('{$fieldName}')->nullable()->comment('{$fieldName}');";
        }

        if ($type === 'string') {
            $max_size_string = config('entity-generator.max_size_string', 50);

            return "\$table->{$type}('{$fieldName}', $max_size_string)->comment('{$fieldName}');";
        }

        return "\$table->{$type}('{$fieldName}')->comment('{$fieldName}');";
    }

    protected function getNonRequiredLine($fieldName, $typeName): string
    {
        $type = explode('-', $typeName)[0];

        return "\$table->{$type}('{$fieldName}')->nullable()->comment('{$fieldName}');";
    }

    protected function generateTable($fields): array
    {
        $resultTable = [];

        foreach ($fields as $typeName => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                array_push($resultTable, $this->getTableRow($fieldName, $typeName));
            }
        }

        return $resultTable;
    }

    protected function getTableRow($fieldName, $typeName): string
    {
        if ($this->isJson($typeName)) {
            return $this->getJsonLine($fieldName);
        }

        if ($this->isRequired($typeName)) {
            return $this->getRequiredLine($fieldName, $typeName);
        }

        if ($this->isNullable($typeName)) {
            return $this->getNonRequiredLine($fieldName, $typeName);
        }

        throw new Exception('Unknown fieldType in MigrationGenerator');
    }
}
