<?php

namespace Gilcleis\Support\Generators;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Gilcleis\Support\Exceptions\ClassNotExistsException;

class TestsGenerator extends EntityGenerator
{
    public function getTestClassName(): string
    {
        return "{$this->model}Test";
    }

    protected bool $withAuth = false;

    public function generate(): void
    {
        if ($this->classExists('tests_apis', "{$this->model}ApiTest")) {
            event(new SuccessCreateMessage("Cannot create {$this->model} ApiTest cause {$this->model}ApiTest already exists."));

            return;
        }
        if (!$this->classExists('controllers', "{$this->model}Controller")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Controller cause {$this->model}Controller does not exists.",
                "Create a {$this->model} Controller by himself or run command 'php artisan make:entity {$this->model} --only-controller'."
            );
        }

        $content = $this->getStub('test_repository', [
            'entity' => $this->model,
            'databaseTableName' => $this->getTableName($this->model),
            'entities' => $this->getTableName($this->model, '-'),
            'withAuth' => $this->withAuth,
            'modelsNamespace' => $this->getOrCreateNamespace('models'),
            'fields' => $this->prepareFields()

        ]);

        $testName = $this->getTestClassName();
        $createMessage = "Created a new Test: {$this->model}RepositoryTest";

        $this->saveClass('tests_repositories', "{$this->model}RepositoryTest", $content);

        event(new SuccessCreateMessage($createMessage));

        $content = $this->getStub('test_model', [
            'entity' => $this->model,
            'databaseTableName' => $this->getTableName($this->model),
            'entities' => $this->getTableName($this->model, '-'),
            'withAuth' => $this->withAuth,
            'modelsNamespace' => $this->getOrCreateNamespace('models'),
            'casts' => $this->getCasts($this->fields),
            'fields' => $this->prepareFields()

        ]);

        $createMessage = "Created a new Test: {$this->model}ModelTest";

        $this->saveClass('tests_models', "{$this->model}ModelTest", $content);

        event(new SuccessCreateMessage($createMessage));

        $content = $this->getStub('test_service', [
            'entity' => $this->model,
            'databaseTableName' => $this->getTableName($this->model),
            'entities' => $this->getTableName($this->model, '-'),
            'withAuth' => $this->withAuth,
            'modelsNamespace' => $this->getOrCreateNamespace('models'),
            'casts' => $this->getCasts($this->fields),
            'fields' => $this->prepareFields()

        ]);

        $createMessage = "Created a new Test: {$this->model}ServiceTest";

        $this->saveClass('tests_services', "{$this->model}ServiceTest", $content);

        event(new SuccessCreateMessage($createMessage));

        $content = $this->getStub('test_api', [
            'entity' => $this->model,
            'databaseTableName' => $this->getTableName($this->model),
            'entities' => $this->getTableName($this->model, '-'),
            'withAuth' => $this->withAuth,
            'modelsNamespace' => $this->getOrCreateNamespace('models'),
            'casts' => $this->getCasts($this->fields),
            'fields' => $this->prepareFields()

        ]);

        $createMessage = "Created a new Test: {$this->model}ApiTest";

        $this->saveClass('tests_apis', "{$this->model}ApiTest", $content);

        event(new SuccessCreateMessage($createMessage));
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
