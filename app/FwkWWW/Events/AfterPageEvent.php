<?php
namespace FwkWWW\Events;

use Fwk\Events\Event;
use FwkWWW\CmsService;
use FwkWWW\PageProvider;
use Symfony\Component\HttpFoundation\Response;

class AfterPageEvent extends Event
{
    const EVENT_NAME = 'afterPage';
    
    public function __construct($pageName, CmsService $service, 
        PageProvider $provider, Response $response, array $params
    ) {
        parent::__construct(self::EVENT_NAME, array(
            'pageName'  => $pageName,
            'service'   => $service,
            'provider'  => $provider,
            'response'  => $response,
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
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
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