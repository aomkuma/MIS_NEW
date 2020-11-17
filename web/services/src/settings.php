<?php

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::INFO,
            'maxFiles' => 90
        ],
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'mis',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'prefix'    => 'mis_',
            'port' => '3306'
        ]
    ],
];
