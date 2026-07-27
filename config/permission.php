<?php

/*
 * Package defaults are merged by Spatie's service provider. Keep only the
 * application-specific overrides here so this configuration never depends on
 * a file path inside vendor/.
 */
return [
    'teams' => true,

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'tenant_id',
    ],
];
