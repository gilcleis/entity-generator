<?php

namespace Gilcleis\Support\Generators;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Gilcleis\Support\Exceptions\ClassAlreadyExistsException;
use Gilcleis\Support\Exceptions\ClassNotExistsException;
use Gilcleis\Support\Events\SuccessCreateMessage;

class ControllerGenerator extends EntityGenerator
{
    public function generate(): void
    {
        // if ($this->classExists('controllers', "{$this->model}Controller")) {
        //     $this->throwFailureException(
        //         ClassAlreadyExistsException::class,
        //         "Cannot create {$this->model}Controller cause {$this->model}Controller already exists.",
        //         "Remove {$this->model}Controller.",
        //     );

        // }

        if (!$this->classExists('services', "{$this->model}Service")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Service cause {$this->model}Service does not exists.",
                "Create a {$this->model}Service by himself.",
            );
        }

        $controllerContent = $this->getControllerContent($this->model);

        $this->saveClass('controllers', "{$this->model}Controller", $controllerContent);

        //$this->createRoutes();

        event(new SuccessCreateMessage("Created a new Controller: {$this->model}Controller"));
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
