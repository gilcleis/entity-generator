<?php

namespace Gilcleis\Support\Commands;

use Gilcleis\Support\Events\SuccessCreateMessage;
use Gilcleis\Support\Exceptions\ClassNotExistsException;
use Gilcleis\Support\Exceptions\EntityCreateException;
use Gilcleis\Support\Generators\EntityGenerator;
use Gilcleis\Support\Generators\FactoryGenerator;
use Gilcleis\Support\Generators\ModelGenerator;
use Gilcleis\Support\Generators\MigrationGenerator;
use Gilcleis\Support\Generators\RepositoryGenerator;
use Gilcleis\Support\Generators\TestsGenerator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use UnexpectedValueException;

/**
 * @property ModelGenerator $modelGenerator
 * @property EventDispatcher $eventDispatcher
  */
class MakeEntityCommand extends Command
{
    public const CRUD_OPTIONS = [
        'C', 'R', 'U', 'D'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity {name : The name of the entity. This name will use as name of models class.}
        
        {--only-api : Set this flag if you want to create resource, controller, route, requests, tests.}
        {--only-model : Set this flag if you want to create only model. This flag is a higher priority than --only-migration, --only-tests and --only-repository.} 
        {--only-migration : Set this flag if you want to create only repository. This flag is a higher priority than --only-tests.}
        {--only-repository : Set this flag if you want to create only repository. This flag is a higher priority than --only-tests and --only-migration.}
        {--only-tests : Set this flag if you want to create only tests.}
        {--only-factory : Set this flag if you want to create only factory.}

        {--methods=CRUD : Set types of methods to create. Affect on routes, requests classes, controller\'s methods and tests methods.} 

        {--i|integer=* : Add integer field to entity.}
        {--I|integer-required=* : Add required integer field to entity. If you want to specify default value you have to do it manually.}
        {--f|float=* : Add float field to entity.}
        {--F|float-required=* : Add required float field to entity. If you want to specify default value you have to do it manually.}
        {--s|string=* : Add string field to entity. Default type is VARCHAR(255) but you can change it manually in migration.}
        {--S|string-required=* : Add required string field to entity. If you want to specify default value ir size you have to do it manually.}
        {--b|boolean=* : Add boolean field to entity.}
        {--B|boolean-required=* : Add boolean field to entity. If you want to specify default value you have to do it manually.}
        {--t|timestamp=* : Add timestamp field to entity.}
        {--T|timestamp-required=* : Add timestamp field to entity. If you want to specify default value you have to do it manually.}
        {--j|json=* : Add json field to entity.}
        
        {--a|has-one=* : Set hasOne relations between you entity and existed entity.}
        {--A|has-many=* : Set hasMany relations between you entity and existed entity.}
        {--e|belongs-to=* : Set belongsTo relations between you entity and existed entity.}
        {--E|belongs-to-many=* : Set belongsToMany relations between you entity and existed entity.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make entity with Model, Repository, Service, Migration, Controller, Resource and Nova Resource.';

    protected $modelGenerator;
    protected $eventDispatcher;
    protected $migrationGenerator;
    protected $repositoryGenerator;
    protected $testGenerator;
    protected $factoryGenerator;
    

    protected $rules = [
        'only' => [
            'only-api' => [ModelGenerator::class],
            'only-model' => [ModelGenerator::class],
            'only-migration' => [MigrationGenerator::class],
            'only-repository' => [RepositoryGenerator::class],
            'only-factory' => [FactoryGenerator::class],
            'only-tests' => [FactoryGenerator::class, TestsGenerator::class],
        ]
    ];

    public $generators = [
        ModelGenerator::class,
        MigrationGenerator::class, 
        RepositoryGenerator::class,
        TestsGenerator::class,
        FactoryGenerator::class,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->modelGenerator = app(ModelGenerator::class);
        $this->eventDispatcher = app(EventDispatcher::class);
        $this->migrationGenerator = app(MigrationGenerator::class);
        $this->repositoryGenerator = app(RepositoryGenerator::class);
        $this->factoryGenerator = app(FactoryGenerator::class);
        $this->testGenerator = app(TestsGenerator::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->validateInput();
        $this->checkConfigs();
        $this->eventDispatcher->listen(SuccessCreateMessage::class, $this->getSuccessMessageCallback());

        try {
            $this->generate();
        } catch (EntityCreateException $e) {
            $this->error($e->getMessage());
        }
    }

    protected function checkConfigs()
    {
        $packageConfigPath = __DIR__ . '/../../config/entity-generator.php';
        $ax = __DIR__ . '/../../config';
        $packageConfigs = require $packageConfigPath;

        $projectConfigs = config('entity-generator');

        $newConfig = $this->outputNewConfig($packageConfigs, $projectConfigs);

        if ($newConfig !== $projectConfigs) {
            $this->comment('Config has been updated');
            Config::set('entity-generator', $newConfig);
            file_put_contents(config_path('entity-generator.php'), "<?php\n\nreturn" . $this->customVarExport($newConfig) . ';');
        }
    }

    protected function outputNewConfig($packageConfigs, $projectConfigs)
    {
        $flattenedPackageConfigs = Arr::dot($packageConfigs);
        $flattenedProjectConfigs = Arr::dot($projectConfigs);

        $newConfig = array_merge($flattenedPackageConfigs, $flattenedProjectConfigs);

        $translations = 'lang/en/validation.php';
        $translations = (version_compare(app()->version(), '9', '>=')) ? $translations : "resources/{$translations}";

        if ($newConfig['paths.translations'] !== $translations) {
            $newConfig['paths.translations'] = $translations;
        }

        $factories = 'database/factories';
        $factories = (version_compare(app()->version(), '8', '>=')) ? $factories : "{$factories}/ModelFactory.php";

        if ($newConfig['paths.factory'] !== $factories) {
            $newConfig['paths.factory'] = $factories;
        }

        $differences = array_diff_key($newConfig, $flattenedProjectConfigs);

        foreach ($differences as $differenceKey => $differenceValue) {
            $this->comment("Key '{$differenceKey}' was missing in your config, we added it with the value '{$differenceValue}'");
        }

        return array_undot($newConfig);
    }

    protected function customVarExport($expression)
    {
        $defaultExpression = var_export($expression, true);

        $patterns = [
            '/array/' => '',
            '/\(/' => '[',
            '/\)/' => ']',
            '/=> \\n/' => '=>',
            '/=>.+\[/' => '=> [',
            '/^ {8}/m' => str_repeat(' ', 10),
            '/^ {6}/m' => str_repeat(' ', 8),
            '/^ {4}/m' => str_repeat(' ', 6),
            '/^ {2}/m' => str_repeat(' ', 4),
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $defaultExpression);
    }

    protected function classExists($path, $name)
    {
        $paths = config('entity-generator.paths');

        $entitiesPath = $paths[$path];

        $classPath = base_path("{$entitiesPath}/{$name}.php");

        return file_exists($classPath);
    }

    protected function validateInput()
    {
        $this->validateOnlyApiOption();
        $this->validateCrudOptions();
    }

    protected function generate()
    {
        foreach ($this->rules['only'] as $option => $generators) {
            if ($this->option($option)) {
                foreach ($generators as $generator) {
                    $this->runGeneration($generator);
                }

                return;
            }
        }

        foreach ($this->generators as $generator) {
            $this->runGeneration($generator);
        }
    }

    protected function runGeneration($generator)
    {
        app($generator)
            ->setModel($this->argument('name'))
            ->setFields($this->getFields())
            ->setRelations($this->getRelations())
            ->setCrudOptions($this->getCrudOptions())
            ->generate();
    }

    protected function getCrudOptions()
    {
        return str_split($this->option('methods'));
    }

    protected function getRelations()
    {
        return [
            'hasOne' => $this->option('has-one'),
            'hasMany' => $this->option('has-many'),
            'belongsTo' => $this->option('belongs-to'),
            'belongsToMany' => $this->option('belongs-to-many')
        ];
    }

    protected function getSuccessMessageCallback()
    {
        return function (SuccessCreateMessage $event) {
            $this->info($event->message);
        };
    }

    protected function getFields()
    {
        return Arr::only($this->options(), EntityGenerator::AVAILABLE_FIELDS);
    }

    protected function validateCrudOptions()
    {
        $crudOptions = $this->getCrudOptions();

        foreach ($crudOptions as $crudOption) {
            if (!in_array($crudOption, MakeEntityCommand::CRUD_OPTIONS)) {
                throw new UnexpectedValueException("Invalid method {$crudOption}.");
            }
        }
    }

    protected function validateOnlyApiOption()
    {
        if ($this->option('only-api')) {
            $modelName = Str::studly($this->argument('name'));
            if (!$this->classExists('services', "{$modelName}Service")) {
                throw new ClassNotExistsException('Cannot create API without entity.');
            }
        }
    }
}
