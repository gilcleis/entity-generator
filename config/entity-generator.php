<?php

return [
    'paths' => [
        'models' => 'app/Models',
        'migrations' => 'database/migrations',
    ],
    'stubs' => [
        'model' => 'entity-generator::model',
        'relation' => 'entity-generator::relation',
        'migration' => 'entity-generator::migration',
    ],
    'items_per_page' => 12,
    'max_size_string' => 60,
];
