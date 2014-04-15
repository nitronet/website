<?php
namespace FwkWWW\Providers\Routes;

use FwkWWW\Providers\RoutesProvider;
use Fwk\Core\Components\UrlRewriter\Route;
use Fwk\Core\Components\UrlRewriter\RouteParameter;
use FwkWWW\CmsService;

class ConfigRoutesProvider implements RoutesProvider
{
    public function getRoutes(CmsService $cms)
    {
        $routes = array();
        $cfg    = $cms->getSiteConfig();
        
        if (!isset($cfg['urls']) || !is_array($cfg['urls'])) {
            return array();
        }
        
        foreach ($cfg['urls'] as $uri => $infos) {
            
            $pageName       = (isset($infos['page']) ? $infos['page'] : null);
            $params         = (isset($infos['parameters']) ? $infos['parameters'] : array());
            $parameters     = array(
                new RouteParameter('page', $pageName, null, true, $pageName)
            );
            
            foreach ($params as $paramName => $data) {
                $parameters[] = new RouteParameter(
                    $paramName, 
                    (isset($data['default']) ? $data['default'] : null), 
                    (isset($data['regex']) ? $data['regex'] : null), 
                    (isset($data['required']) ? $data['required'] : false)
                );
            }
            
            $route = new Route('PageView', $uri, $parameters);
            var_dump($route);
            $routes[] = $route;
        }
        
        return $routes;
    }
}