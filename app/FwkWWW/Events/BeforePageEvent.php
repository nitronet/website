<?php
namespace FwkWWW\Events;

use Fwk\Events\Event;
use FwkWWW\CmsService;
use FwkWWW\PageProvider;

class BeforePageEvent extends Event
{
    const EVENT_NAME = 'beforePage';
    
    public function __construct($pageName, CmsService $service, 
        PageProvider $provider, array $config, array $params
    ) {
        parent::__construct(self::EVENT_NAME, array(
            'pageName'  => $pageName,
            'service'   => $service,
            'provider'  => $provider,
            'config'    => $config,
            'parameters'    => $params
        ));
    }
    
    /**
     * 
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }
    
    /**
     * 
     * @return CmsService
     */
    public function getService()
    {
        return $this->service;
    }
    
    /**
     * 
     * @return PageProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
    
    /**
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}