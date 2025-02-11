<?php

namespace Gilcleis\Support\Generators;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Gilcleis\Support\Exceptions\ClassNotExistsException;

class RepositoryGenerator extends EntityGenerator
{
    public function generate(): void
    {
        $this->checkModelExists();

        if ($this->checkRepositoryExists()) {
           return;
        }

        $repositoryContent = $this->getStub('repository', [
            'entity' => $this->model,
            'namespace' => $this->getOrCreateNamespace('repositories'),
            'modelNamespace' => $this->getOrCreateNamespace('models')
        ]);

        $this->saveClass('repositories', "{$this->model}Repository", $repositoryContent);
        event(new SuccessCreateMessage("Created a new Repository: {$this->model}Repository"));

        if ($this->classExists('repositories', 'BaseRepository')) {
            event(new SuccessCreateMessage("BaseRepository already exists"));
        } else {
            $repositoryContent = $this->getStub('base_repository', [
                'namespace' => $this->getOrCreateNamespace('repositories'),
            ]);

            $this->saveClass('base_repository', "BaseRepository", $repositoryContent);

            event(new SuccessCreateMessage("Created BaseRepository"));
        }

        if ($this->classExists('repositories', 'SearchBaseRepository')) {
            event(new SuccessCreateMessage("SearchBaseRepository already exists"));
        } else {
            $repositoryContent = $this->getStub('search_base_repository', [
                'namespace' => $this->getOrCreateNamespace('repositories'),
            ]);

            $this->saveClass('search_base_repository', "SearchBaseRepository", $repositoryContent);

            event(new SuccessCreateMessage("Created SearchBaseRepository"));
        }

        if ($this->classExists('contracts', "{$this->model}RepositoryInterface")) {
            event(new SuccessCreateMessage("{$this->model}RepositoryInterface already exists"));
        } else {
            $repositoryContent = $this->getStub('contract', [
                'entity' => $this->model,
                'namespace' => $this->getOrCreateNamespace('contracts'),
                'modelNamespace' => $this->getOrCreateNamespace('models')
            ]);

            $this->saveClass('contracts', "{$this->model}RepositoryInterface", $repositoryContent);

            event(new SuccessCreateMessage("Created {$this->model}RepositoryInterface"));
        }
    }

    public function checkModelExists(): void
    {
        if (!$this->classExists('models', $this->model)) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model} Model cause {$this->model} Model does not exists.",
                "Create a {$this->model} Model by himself or run command 'php artisan make:entity {$this->model} --only-model'."
            );
        }
    }

    public function checkRepositoryExists(): bool
    {
        if ($this->classExists('repositories', "{$this->model}Repository")) {
            event(new SuccessCreateMessage("Cannot create {$this->model} Repository cause {$this->model} Repository already exists."));

            return true;
        }

        return false;
    }
}
