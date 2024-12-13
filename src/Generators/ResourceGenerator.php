<?php

namespace Gilcleis\Support\Generators;

use Illuminate\Support\Arr;
use Gilcleis\Support\Events\SuccessCreateMessage;
use Gilcleis\Support\Exceptions\ClassAlreadyExistsException;

class ResourceGenerator extends EntityGenerator
{
    public function generate(): void
    {
        $this->generateResource();
        $this->generateCollectionResource();
    }

    public function generateCollectionResource(): void
    {
        $pluralName = $this->getPluralName($this->model);

        // if ($this->classExists('resources', "{$pluralName}CollectionResource")) {
        //     $this->throwFailureException(
        //         ClassAlreadyExistsException::class,
        //         "Cannot create {$pluralName}CollectionResource cause {$pluralName}CollectionResource already exists.",
        //         "Remove {$pluralName}CollectionResource."
        //     );
        // }

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
        // if ($this->classExists('resources', "{$this->model}Resource")) {
        //     $this->throwFailureException(
        //         ClassAlreadyExistsException::class,
        //         "Cannot create {$this->model}Resource cause {$this->model}Resource already exists.",
        //         "Remove {$this->model}Resource."
        //     );
        // }
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
