<?php

namespace Gilcleis\Support\Generators;

use Illuminate\Support\Arr;
use Gilcleis\Support\Events\SuccessCreateMessage;

class TestsGenerator extends EntityGenerator
{
    public function getTestClassName(): string
    {
        return "{$this->model}Test";
    }

    protected bool $withAuth = false;

    public function generate(): void
    {
        // $content = $this->getStub('test', [
        //     'entity' => $this->model,
        //     'databaseTableName' => $this->getTableName($this->model),
        //     'entities' => $this->getTableName($this->model, '-'),
        //     'withAuth' => $this->withAuth,
        //     'modelsNamespace' => $this->getOrCreateNamespace('models'),
        //     'fields' => $this->prepareFields()

        // ]);

        // $testName = $this->getTestClassName();
        // $createMessage = "Created a new Test: {$testName}";

        // $this->saveClass('tests', $testName, $content);

        // event(new SuccessCreateMessage($createMessage));

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
            'fields' => $this->prepareFields()

        ]);

        $createMessage = "Created a new Test: {$this->model}ModelTest";

        $this->saveClass('tests_models', "{$this->model}ModelTest", $content);

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
                    'condition' => isset($explodedType[1]) ? $explodedType[1] : 'required',
                ];
            }
        }

        return $result;
    }


}
