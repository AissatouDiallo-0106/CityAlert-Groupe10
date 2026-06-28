<?php
declare(strict_types=1);

return [
    'app' => [
        'name'     => ' CITYalert-groupe10',
        'env'      => 'dev',
        'timezone' => 'Africa/Dakar',
    ],
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3307,
        'name'    => 'cityalert',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name'     => 'cityalert_sess',
        'lifetime' => 7200,
    ],
];