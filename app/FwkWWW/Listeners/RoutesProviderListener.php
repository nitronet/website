<?php
namespace FwkWWW\Listeners;

use Fwk\Core\Events\RequestEvent;
use FwkWWW\Providers\RoutesProvider;
use Fwk\Core\Components\UrlRewriter\UrlRewriterService;
use Fwk\Core\Components\UrlRewriter\Route;
use Fwk\Core\Components\UrlRewriter\RouteParameter;

class RoutesProviderListener
{
    /**
     * The RoutesProvider service name (Di)
     * @var string
     */
    protected $providerService;
    
    protected $rewriterService;
    
    protected $cmsService;
    
    /**
     * Constructor
     * 
     * @param RoutesProvider $provider
     */
    public function __construct($providerServiceName, $rewriterServiceName,
        $cmsServiceName
    ) {
        $this->providerService  = $providerServiceName;
        $this->rewriterService  = $rewriterServiceName;
        $this->cmsService       = $cmsServiceName;
    }
    
    public function onRequest(RequestEvent $event)
    {
        $urlRewriter    = $event->getApplication()
                            ->getServices()
                            ->get($this->rewriterService);
        $cmsService     = $event->getApplication()
                            ->getServices()
                            ->get($this->cmsService);
        $routes         = $event->getApplication()
                            ->getServices()
                            ->get($this->providerService)
                            ->getRoutes($cmsService);
        
        $urlRewriter->addRoutes($routes);
    }
}