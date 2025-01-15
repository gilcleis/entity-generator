<?php

namespace Gilcleis\Support\Generators;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Illuminate\Support\Arr;

class ResourceGenerator extends EntityGenerator
{
    public function generate(): void
    {
        $this->generateResource();
        $this->generateCollectionResource();
    }

    public function checkCollectionExists(): bool
    {
        if ($this->classExists('resources', "{$this->model}Collection")) {
            event(new SuccessCreateMessage("Cannot create {$this->model} Collection cause {$this->model}Collection already exists."));

            return true;
        }

        return false;
    }

    public function checkResourceExists(): bool
    {
        if ($this->classExists('resources', "{$this->model}Resource")) {
            event(new SuccessCreateMessage("Cannot create {$this->model} Resource cause {$this->model}Resource already exists."));

            return true;
        }

        return false;
    }

    public function generateCollectionResource(): void
    {
        $pluralName = $this->getPluralName($this->model);

        if ($this->checkCollectionExists()) {
            return;
        }

        $collectionResourceContent = $this->getStub('collection_resource', [
            'singular_name' => $this->model,
            'plural_name' => $pluralName,
            'namespace' => $this->getOrCreateNamespace('resources')
        ]);

        $this->saveClass('resources', "{$pluralName}CollectionResource", $collectionResourceContent);

        event(new SuccessCreateMessage("Created a new CollectionResource: {$pluralName}CollectionResource"));
    }

    public function generateResource(): void
    {
        if ($this->checkResourceExists()) {
            return;
        }

        $resourceContent = $this->getStub('resource', [
            'entity' => $this->model,
            'namespace' => $this->getOrCreateNamespace('resources'),
            'fields' => Arr::collapse($this->fields),
            'relations' => $this->relations,
        ]);

        $this->saveClass('resources', "{$this->model}Resource", $resourceContent);

        event(new SuccessCreateMessage("Created a new Resource: {$this->model}Resource"));
    }
}
