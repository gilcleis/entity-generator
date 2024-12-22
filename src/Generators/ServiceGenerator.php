<?php

namespace Gilcleis\Support\Generators;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Gilcleis\Support\Exceptions\ClassNotExistsException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ServiceGenerator extends EntityGenerator
{
    public function setRelations($relations)
    {
        foreach ($relations['belongsTo'] as $field) {
            $name = Str::snake($field) . '_id';

            $this->fields['integer'][] = $name;
        }

        return $this;
    }

    public function generate(): void
    {
        if (!$this->classExists('repositories', "{$this->model}Repository")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model} Repository cause {$this->model} Repository does not exists.",
                "Create a {$this->model} Repository by himself or run command 'php artisan make:entity {$this->model} --only-repository'."
            );
        }

        if ($this->classExists('services', "{$this->model}Service")) {
            event(new SuccessCreateMessage("Cannot create {$this->model} Service cause {$this->model} Service already exists."));

            return;
        }
        $stub = 'service';
        $serviceContent = $this->getStub($stub, [
            'entity' => $this->model,
            'fields' => $this->getFields(),
            'namespace' => $this->getOrCreateNamespace('services'),
            'repositoriesNamespace' => $this->getOrCreateNamespace('repositories'),
            'modelsNamespace' => $this->getOrCreateNamespace('models')
        ]);

        $this->saveClass('services', "{$this->model}Service", $serviceContent);

        event(new SuccessCreateMessage("Created a new Service: {$this->model}Service"));
    }

    protected function getFields(): array
    {
        $simpleSearch = Arr::only($this->fields, ['integer', 'integer-required', 'boolean', 'boolean-required']);

        return [
            'simple_search' => Arr::collapse($simpleSearch),
            'search_by_query' => array_merge($this->fields['string'], $this->fields['string-required'])
        ];
    }
}
