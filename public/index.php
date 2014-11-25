<?php
require_once __DIR__ .'/../vendor/autoload.php';

use Fwk\Core\Components\Descriptor\Descriptor;

ob_start("ob_gzhandler");

$desc = new Descriptor(__DIR__ .'/../app/fwk.xml');
$app = $desc->execute('FwkWWW');

$app->setDefaultAction('Home');

$services = $app->getServices();

$app->plugin(new \Fwk\Core\Plugins\RequestMatcherPlugin())
    ->plugin(new \Fwk\Core\Plugins\UrlRewriterPlugin())
    ->plugin(new \Fwk\Core\Plugins\ResultTypePlugin())
    ->plugin(new \Fwk\Core\Plugins\ViewHelperPlugin());

$app->plugin(new \Nitronet\Fwk\Assetic\AsseticPlugin(array(
    'directory' => $services->getProperty('assetic.assets.directory'),
    'debug' => $services->getProperty('assetic.debug', true),
    'action' => $services->getProperty('assetic.action.name'),
    'cache' => (bool)$services->getProperty('assetic.use.cache', true),
    'cacheDir' => $services->getProperty('assetic.cache.directory'),
    'cacheStrategy' => $services->getProperty('assetic.cache.strategy'),
    'helperName' => 'asset'
),
array(
    'bower' => __DIR__ .'/../site/bower_components'
)));

$response = $app->run();
if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
    $response->send();
}
