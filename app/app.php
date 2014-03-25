<?php

use Fwk\Core\Components\Descriptor\Descriptor;

$desc = new Descriptor(__DIR__ .'/fwk.xml');
$app = $desc->execute('FwkWWW');
$app->setDefaultAction('Home');

return $app;