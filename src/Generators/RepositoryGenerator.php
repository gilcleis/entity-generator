<?php

namespace Gilcleis\Support\Generators;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Gilcleis\Support\Exceptions\ClassAlreadyExistsException;
use Gilcleis\Support\Exceptions\ClassNotExistsException;

class RepositoryGenerator extends EntityGenerator
{
    public function generate(): void
    {
        // if (!$this->classExists('models', $this->model)) {
        //     $this->throwFailureException(
        //         ClassNotExistsException::class,
        //         "Cannot create {$this->model} Model cause {$this->model} Model does not exists.",
        //         "Create a {$this->model} Model by himself or run command 'php artisan make:entity {$this->model} --only-model'."
        //     );
        // }

        if ($this->classExists('repositories', "{$this->model}Repository")) {
            $this->throwFailureException(
                ClassAlreadyExistsException::class,
                "Cannot create {$this->model} Repository cause {$this->model} Repository already exists.",
                "Remove {$this->model} Repository."
            );
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
}
