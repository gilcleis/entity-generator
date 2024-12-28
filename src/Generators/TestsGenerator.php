<?php

namespace Gilcleis\Support\Generators;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Gilcleis\Support\Exceptions\ClassNotExistsException;
use Illuminate\Support\Str;

class TestsGenerator extends EntityGenerator
{
    protected bool $withAuth = false;
    protected array $testTypes = ['repository', 'model', 'service', 'api'];

    // public function getTestClassName(): string
    // {
    //     return "{$this->model}Test";
    // }

    public function generate(): void
    {
        $this->validateApiTestExistence();
        $this->validateControllerExistence();

        foreach ($this->testTypes as $testType) {
            $this->generateTest($testType);
        }
    }

    protected function validateApiTestExistence(): void
    {
        if ($this->classExists('tests_apis', "{$this->model}ApiTest")) {
            event(new SuccessCreateMessage("Cannot create {$this->model} ApiTest cause {$this->model}ApiTest already exists."));
            return;
        }
    }

    protected function validateControllerExistence(): void
    {
        if (!$this->classExists('controllers', "{$this->model}Controller")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Controller cause {$this->model}Controller does not exists.",
                "Create a {$this->model} Controller by himself or run command 'php artisan make:entity {$this->model} --only-controller'."
            );
        }
    }

    protected function generateTest(string $testType): void
    {
        $content = $this->getTestContent($testType);
        $className = $this->getTestClassName($testType);
        $folderName = $this->getTestFolderName($testType);

        $this->saveClass($folderName, $className, $content);

        event(new SuccessCreateMessage("Created a new Test: {$className}"));
    }

    protected function getTestContent(string $testType): string
    {
        return $this->getStub("test_{$testType}", [
            'entity' => $this->model,
            'databaseTableName' => $this->getTableName($this->model),
            'entities' => $this->getTableName($this->model, '-'),
            'withAuth' => $this->withAuth,
            'modelsNamespace' => $this->getOrCreateNamespace('models'),
            'casts' => $this->getCasts($this->fields),
            'fields' => $this->prepareFields()
        ]);
    }

    protected function getTestClassName(string $testType): string
    {
        return "{$this->model}" . ucfirst($testType) . "Test";
    }

    protected function getTestFolderName(string $testType): string
    {
        return "tests_".Str::plural($testType) ;
    }

    protected function prepareFields(): array
    {
        $result = [];

        foreach ($this->fields as $type => $fields) {
            foreach ($fields as $field) {
                $explodedType = explode('-', $type);

                $result[] = [
                    'name' => $field,
                    'type' => head($explodedType),
                    'condition' => $explodedType[1] ?? 'required',
                ];
            }
        }

        return $result;
    }
}
