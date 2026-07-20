<?php

return [
    'role' => env('APP_ROLE', 'local'), // 'local' or 'cloud'
    'cloud_api_url' => env('CLOUD_SYNC_API_URL', 'http://cloud-server.test'),
    'cloud_token' => env('CLOUD_SYNC_TOKEN', 'default-secure-token-12345'),
];
