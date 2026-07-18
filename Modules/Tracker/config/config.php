<?php

return [
    'name' => 'Tracker',
    'web_updates' => env('TRACKER_WEB_UPDATES', env('APP_ENV', 'production') === 'local'),
];
