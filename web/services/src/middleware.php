<?php

include 'LogMiddleware.php';
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
$container = $app->getContainer();

$app->add(new LogMiddleware($container));

$checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
$trustedProxies = ['10.0.0.1', '10.0.0.2']; // Note: Never trust the IP address for security processes!
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));
