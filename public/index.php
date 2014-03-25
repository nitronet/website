<?php
require_once __DIR__ .'/../vendor/autoload.php';

$app = include __DIR__ .'/../app/app.php';

$response = $app->run();
if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
    $response->send();
} 