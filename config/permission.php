<?php

$permission = require base_path('vendor/spatie/laravel-permission/config/permission.php');

$permission['teams'] = true;
$permission['column_names']['team_foreign_key'] = 'tenant_id';

return $permission;
