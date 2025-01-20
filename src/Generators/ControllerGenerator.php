<?php

namespace Gilcleis\Support\Generators;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Gilcleis\Support\Exceptions\ClassNotExistsException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ControllerGenerator extends EntityGenerator
{
    

    public function generate(): void
    {
        if ($this->checkControllerExists()) {
            return;
        }

        $this->checkServiceExists();
        $this->checkRequestExists();
        $this->checkResourceExists();
        $this->checkCollectionExists();

        $controllerContent = $this->getControllerContent($this->model);

        $this->saveClass('controllers', "{$this->model}Controller", $controllerContent);

        $this->createRoutes();

        event(new SuccessCreateMessage("Created a new Controller: {$this->model}Controller"));
    }

    protected function checkControllerExists(): bool
    {
        if ($this->classExists('controllers', "{$this->model}Controller")) {
            event(new SuccessCreateMessage("Cannot create {$this->model} Controller cause {$this->model}Controller already exists."));

            return true;
        }

        return false;
    }

    protected function checkServiceExists(): void
    {
        if (!$this->classExists('services', "{$this->model}Service")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Service cause {$this->model}Service does not exists.",
                "Create a {$this->model} Service by himself or run command 'php artisan make:entity {$this->model} --only-service'."
            );
        }
    }

    protected function checkRequestExists(): void
    {
        if (!$this->classExists('requests', "{$this->model}Request")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Request cause {$this->model}Request does not exists.",
                "Create a {$this->model} Request by himself or run command 'php artisan make:entity {$this->model} --only-requests'."
            );
        }
    }

    protected function checkResourceExists(): void
    {
        if (!$this->classExists('resources', "{$this->model}Resource")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Resource cause {$this->model}Resource does not exists.",
                "Create a {$this->model} Resource by himself or run command 'php artisan make:entity {$this->model} --only-resource'."
            );
        }
    }

    protected function checkCollectionExists(): void
    {
        if (!$this->classExists('resources', "{$this->model}Collection")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Collection cause {$this->model}Collection does not exists.",
                "Create a {$this->model} Collection by himself or run command 'php artisan make:entity {$this->model} --only-resource'."
            );
        }
    }

    protected function getControllerContent($model): string
    {
        return $this->getStub('controller', [
            'entity' => $model,
            'requestsFolder' => $this->getPluralName($model),
            'namespace' => $this->getOrCreateNamespace('controllers'),
            'requestsNamespace' => $this->getOrCreateNamespace('requests'),
            'resourcesNamespace' => $this->getOrCreateNamespace('resources'),
            'servicesNamespace' => $this->getOrCreateNamespace('services'),
        ]);
    }

    protected function createRoutes(): void
    {
        $routesPath = base_path($this->paths['routes']);

        if (!file_exists($routesPath)) {
            $this->throwFailureException(
                FileNotFoundException::class,
                "Not found file with routes.",
                "Create a routes file on path: '{$routesPath}'.",
            );
        }

        $this->addUseController($routesPath);
        $this->addRoutes($routesPath);
    }

    protected function addRoutes($routesPath): string
    {
        $routesContent = $this->getStub('routes', [
            'entity' => $this->model,
            'withAuth' => $this->withAuth,
            'entities' => $this->getTableName($this->model, '-'),
        ]);

        $routes = explode("\n", $routesContent);

        foreach ($routes as $route) {
            if (!empty($route)) {
                $createMessage = "Created a new Route: $route";

                event(new SuccessCreateMessage($createMessage));
            }
        }

        return file_put_contents($routesPath, "\n\n{$routesContent}", FILE_APPEND);
    }

    protected function addUseController(string $routesPath): void
    {
        $routesFileContent = file_get_contents($routesPath);

        $stub = $this->getStub('use_routes', [
            'namespace' => $this->getOrCreateNamespace('controllers'),
            'entity' => $this->model
        ]);

        $routesFileContent = preg_replace('/\<\?php[^A-Za-z]*/', "<?php\n\n{$stub}", $routesFileContent);

        file_put_contents($routesPath, $routesFileContent);
    }
}
