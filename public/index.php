<?php
namespace App;

require_once __DIR__ .'/../vendor/autoload.php';

use Fwk\Core\Components\Descriptor\Descriptor;
use Fwk\Core\Plugins\RequestMatcherPlugin;
use Fwk\Core\Plugins\ResultTypePlugin;
use Fwk\Core\Plugins\UrlRewriterPlugin;
use Fwk\Core\Plugins\ViewHelperPlugin;
use Nitronet\Fwk\Assetic\AsseticPlugin;
use Nitronet\Fwk\Twig\TwigPlugin;
use Symfony\Component\HttpFoundation\Response;

$desc = new Descriptor(__DIR__ .'/../app/fwk.xml');
$app = $desc->execute('FwkWWW')
    ->setDefaultAction('Home');

$services = $app->getServices();

$app->plugin(new RequestMatcherPlugin())
    ->plugin(new UrlRewriterPlugin())
    ->plugin(new ResultTypePlugin())
    ->plugin(new ViewHelperPlugin());

$app->plugin(new AsseticPlugin(array(
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

$app->plugin(new TwigPlugin(array(
    'directory' => $services->getProperty('twig.templates.dir'),
    'debug' => true,
    'twig' => array(
        'debug' => $services->getProperty('twig.debug', false),
        'cache' => $services->getProperty('twig.cache.dir', null)
    )
)));

ob_start("ob_gzhandler");

$response = $app->run();
if ($response instanceof Response) {
    $response->send();
}
