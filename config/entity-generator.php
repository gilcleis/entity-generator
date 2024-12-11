<?php

return [
    'paths' => [
        'models' => 'app/Models',
        'migrations' => 'database/migrations',
        'repositories' => 'app/Repositories',
        'traits' => 'app/Traits',
    ],
    'stubs' => [
        'model' => 'entity-generator::model',
        'relation' => 'entity-generator::relation',
        'migration' => 'entity-generator::migration',
        'repository' => 'entity-generator::repository',
        'trait' => 'entity-generator::trait',
    ],
    'items_per_page' => 12,
    'max_size_string' => 60,
];
