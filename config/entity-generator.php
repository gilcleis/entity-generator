<?php

return [
    'paths' => [
        'models' => 'app/Models',
        'migrations' => 'database/migrations',
        'repositories' => 'app/Repositories',
        'traits' => 'app/Traits',
        'tests_repository' => 'tests/Feature/Repositories',
        'tests_models' => 'tests/Feature/Models',
        'contracts' => 'app/Repositories/Contracts',
        'factory' => 'database/factories',
        'tests' => 'tests/Feature',
    ],
    'stubs' => [
        'model' => 'entity-generator::model',
        'relation' => 'entity-generator::relation',
        'migration' => 'entity-generator::migration',
        'repository' => 'entity-generator::repository',
        'trait' => 'entity-generator::trait',
        'contract' => 'entity-generator::contract',
        'factory' => 'entity-generator::factory',
        'test' => 'entity-generator::test',
        'test_repository' => 'entity-generator::test_repository',
        'test_model' => 'entity-generator::test_model',
    ],
    'items_per_page' => 12,
    'max_size_string' => 60,
];
