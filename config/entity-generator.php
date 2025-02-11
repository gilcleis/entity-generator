<?php

return [
    'paths' => [
        'base_repository' => 'app/Repositories',
        'contracts' => 'app/Repositories/Contracts',
        'controllers' => 'app/Http/Controllers/Api',
        'database_seeder' => 'database/seeds/DatabaseSeeder.php',
        'dto' => 'app/Dtos',
        'factory' => 'database/factories',
        'migrations' => 'database/migrations',
        'models' => 'app/Models',
        'repositories' => 'app/Repositories',
        'requests' => 'app/Http/Requests',
        'resources' => 'app/Http/Resources',
        'routes' => 'routes/api.php',
        'search_base_repository' => 'app/Repositories',
        'seeders' => 'database/seeders',
        'services' => 'app/Services',
        'tests_apis' => 'tests/Feature/Api',
        'tests_models' => 'tests/Feature/Models',
        'tests_repositories' => 'tests/Feature/Repositories',
        'tests_services' => 'tests/Feature/Service',
        'tests' => 'tests/Feature',
        'traits' => 'app/Traits',
        'translations' => 'lang/en/validation.php',
        'use_routes' => 'entity-generator::use_routes',        
    ],
    'stubs' => [
        'base_repository' => 'entity-generator::base_repository',
        'collection_resource' => 'entity-generator::collection_resource',
        'contract' => 'entity-generator::contract',
        'controller' => 'entity-generator::controller',
        'database_empty_seeder' => 'entity-generator::database_empty_seeder',
        'dto' => 'entity-generator::dto',

        'empty_factory' => 'entity-generator::empty_factory',
        'factory' => 'entity-generator::factory',
        'legacy_empty_factory' => 'entity-generator::legacy_empty_factory',
        'legacy_factory' => 'entity-generator::legacy_factory',
        'legacy_seeder' => 'entity-generator::legacy_seeder',
        'migration' => 'entity-generator::migration',
        'model_trait' => 'entity-generator::model_trait',
        'model' => 'entity-generator::model',
        'relation' => 'entity-generator::relation',
        'repository' => 'entity-generator::repository',
        'request' => 'entity-generator::request',
        'resource' => 'entity-generator::resource',
        'routes' => 'entity-generator::routes',
        'search_base_repository' => 'entity-generator::search_base_repository',
        'seeder' => 'entity-generator::seeder',
        'service' => 'entity-generator::service',
        'test_api' => 'entity-generator::test_api',
        'test_model' => 'entity-generator::test_model',
        'test_repository' => 'entity-generator::test_repository',
        'test_service' => 'entity-generator::test_service',
        'test' => 'entity-generator::test',
        'trait' => 'entity-generator::trait',
        'translation_not_found' => 'entity-generator::translation_not_found',
        'use_routes' => 'entity-generator::use_routes',
        'validation' => 'entity-generator::validation',
    ],
    'items_per_page' => 12,
    'max_size_string' => 60,
];
