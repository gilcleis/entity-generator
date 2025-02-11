<?php

namespace Gilcleis\Support\Generators;

use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Gilcleis\Support\Exceptions\ModelFactoryNotFound;
use Gilcleis\Support\Exceptions\ClassNotExistsException;
use Gilcleis\Support\Exceptions\ModelFactoryNotFoundedException;
use Gilcleis\Support\Exceptions\ClassAlreadyExistsException;
use Gilcleis\Support\Events\SuccessCreateMessage;
use Exception;

class FactoryGenerator extends EntityGenerator
{
    const array FAKERS_METHODS = [
        'integer' => 'randomNumber()',
        'boolean' => 'boolean',
        'string' => 'word',
        'float' => 'randomFloat(2, 0, 10000)',
        'timestamp' => 'dateTime->format(\'Y-m-d H:i:s\')',
    ];

    const array CUSTOM_METHODS = [
        'json' => '[]',
    ];

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

    public function checkFactoryExists(): bool
    {
        if ($this->classExists('factory', "{$this->model}Factory")) {
            event(new SuccessCreateMessage("Cannot create {$this->model} Factory cause {$this->model}Factory already exists."));

            return true;
        }

        return false;
    }

    protected function generateSeparateClass(): string
    {
        $this->checkModelExists();

        if ($this->checkFactoryExists()) {
            return '';
        }

  
        $factoryContent = $this->getStub('factory', [
            'namespace' => $this->getOrCreateNamespace('factory'),
            'entity' => $this->model,
            'fields' => $this->prepareFields()
        ]);

        $this->saveClass('factory', "{$this->model}Factory", $factoryContent);

        return "Created a new Factory: {$this->model}Factory";
    }

    protected function generateToGenericClass(): string
    {
        if (!file_exists($this->paths['factory'])) {
            $this->prepareEmptyFactory();
        }

        if (!$this->checkExistModelFactory() && $this->checkExistRelatedModelsFactories()) {
            $stubPath = config("entity-generator.stubs.factory");

            $content = view($stubPath)->with([
                'entity' => $this->model,
                'fields' => $this->prepareFields(),
                'modelsNamespace' => $this->getOrCreateNamespace('models')
            ])->render();

            $content = "\n\n" . $content;

            $createMessage = "Created a new Test factory for {$this->model} model in '{$this->paths['factory']}'";

            file_put_contents($this->paths['factory'], $content, FILE_APPEND);

            $this->prepareRelatedFactories();
        } else {
            $createMessage = "Factory for {$this->model} model has already created, so new factory not necessary create.";
        }

        return $createMessage;
    }

    public function generate(): void
    {
        $createMessage = (version_compare(app()->version(), '8', '>='))
            ? $this->generateSeparateClass()
            : $this->generateToGenericClass();

        event(new SuccessCreateMessage($createMessage));
    }

    protected function prepareEmptyFactory(): void
    {
        $stubPath = config('entity-generator.stubs.factory');
        $content = "<?php \n\n" . view($stubPath, [
            'modelsNamespace' => $this->getOrCreateNamespace('models')
        ])->render();

        list($basePath, $databaseFactoryDir) = extract_last_part(config('entity-generator.paths.factory'), '/');

        if (!is_dir($databaseFactoryDir)) {
            mkdir($databaseFactoryDir);
        }

        file_put_contents($this->paths['factory'], $content);
    }

    protected function checkExistRelatedModelsFactories(): bool
    {
        $modelFactoryContent = file_get_contents($this->paths['factory']);
        $relatedModels = $this->getRelatedModels($this->model);
        $modelNamespace = $this->getOrCreateNamespace('models');

        foreach ($relatedModels as $relatedModel) {
            $relatedFactoryClass = "{$modelNamespace}\\$relatedModel::class";
            $existModelFactory = strpos($modelFactoryContent, $relatedFactoryClass);

            if (!$existModelFactory) {
                $this->throwFailureException(
                    ModelFactoryNotFoundedException::class,
                    "Not found $relatedModel factory for $relatedModel model in '{$this->paths['factory']}",
                    "Please declare a factory for $relatedModel model on '{$this->paths['factory']}' path and run your command with option '--only-tests'."
                );
            }
        }

        return true;
    }

    protected static function getFakerMethod($field): string
    {
        if (Arr::has(self::FAKERS_METHODS, $field['type'])) {
            return "\$faker->" . self::FAKERS_METHODS[$field['type']];
        }

        return self::getCustomMethod($field);
    }

    protected static function getCustomMethod($field): string
    {
        if (Arr::has(self::CUSTOM_METHODS, $field['type'])) {
            return self::CUSTOM_METHODS[$field['type']];
        }

        $message = $field['type'] . 'not found in CUSTOM_METHODS variable CUSTOM_METHODS = ' . self::CUSTOM_METHODS;
        throw new Exception($message);
    }

    protected function prepareRelatedFactories(): void
    {
        $relations = array_merge(
            $this->relations['hasOne'],
            $this->relations['hasMany']
        );

        foreach ($relations as $relation) {
            $modelFactoryContent = file_get_contents($this->paths['factory']);

            if (!Str::contains($modelFactoryContent, $this->getModelClass($relation))) {
                $this->throwFailureException(
                    ModelFactoryNotFound::class,
                    "Model factory for mode {$relation} not found.",
                    "Please create it and after thar you can run this command with flag '--only-tests'."
                );
            }

            $matches = [];

            preg_match($this->getFactoryPattern($relation), $modelFactoryContent, $matches);

            foreach ($matches as $match) {
                $field = Str::snake($this->model) . '_id';

                $newField = "\n        \"{$field}\" => 1,";

                $modelFactoryContent = str_replace($match, $match . $newField, $modelFactoryContent);
            }

            file_put_contents($this->paths['factory'], $modelFactoryContent);
        }
    }

    public static function getFactoryFieldsContent($field): string
    {
        /** @var Faker $faker */
        $faker = app(Faker::class);

        if (preg_match('/_id$/', $field['name']) || ($field['name'] == 'id')) {
            return 1;
        }

        if (property_exists($faker, $field['name'])) {
            return "\$faker-\>{$field['name']}";
        }

        if (method_exists($faker, $field['name'])) {
            return "\$faker-\>{$field['name']}()";
        }

        return self::getFakerMethod($field);
    }

    protected function checkExistModelFactory(): int
    {
        $modelFactoryContent = file_get_contents($this->paths['factory']);
        $modelNamespace = $this->getOrCreateNamespace('models');
        $factoryClass = "{$modelNamespace}\\$this->model::class";

        return strpos($modelFactoryContent, $factoryClass);
    }

    protected function prepareFields(): array
    {
        $result = [];

        foreach ($this->fields as $type => $fields) {
            foreach ($fields as $field) {
                $explodedType = explode('-', $type);

                $result[] = [
                    'name' => $field,
                    'type' => head($explodedType)
                ];
            }
        }

        return $result;
    }

    protected function getFactoryPattern($model): string
    {
        $modelNamespace = "App\\\\Models\\\\" . $model;
        $return = "return \\[";

        return "/{$modelNamespace}.*{$return}/sU";
    }

    protected function getModelClass($model): string
    {
        $modelNamespace = $this->getOrCreateNamespace('models');

        return "{$modelNamespace}\\{$model}";
    }

    protected function getRelatedModels($model)
    {
        $content = $this->getModelClassContent($model);

        preg_match_all('/(?<=belongsTo\().*(?=::class)/', $content, $matches);

        return head($matches);
    }

    protected function getModelClassContent($model): string
    {
        $path = base_path("{$this->paths['models']}/{$model}.php");

        if (!$this->classExists('models', $model)) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$model} Model cause {$model} Model does not exists.",
                "Create a {$model} Model by himself or run command 'php artisan make:entity {$model} --only-model'."
            );
        }

        return file_get_contents($path);
    }
}
