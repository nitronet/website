<?php
namespace FwkWWW;

use Fwk\Core\Context;

interface PageProvider
{
    public function has($pageName, array $config);
    
    public function getConfig($pageName, array $config);
    
    public function render($pageName, Context $context, array $config, array $params = array());
    
    public function getLastModified($pageName, Context $context, array $config);
}