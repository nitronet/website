<?php
require_once __DIR__ .'/../vendor/autoload.php';

use Fwk\Core\Components\Descriptor\Descriptor;

ob_start("ob_gzhandler");

$desc = new Descriptor(__DIR__ .'/../app/fwk.xml');
$app = $desc->execute('FwkWWW');
$app->setDefaultAction('Home');

$response = $app->run();
if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
    $response->send();
}
