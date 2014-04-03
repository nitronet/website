<?php
namespace FwkWWW\Controllers;

use Fwk\Core\Action\Result;
use Fwk\Core\Action\Controller;
use Fwk\Core\Preparable;

class PageView extends Controller implements Preparable
{
    public $page;
    
    protected $config;
   
    public function prepare()
    {
        $service    = $this->getCmsService();
        $cfg        = $service->getSiteConfig();
        
        if (empty($this->page)) {
            $this->page = $cfg['homepage'];
        }
    }
    
    public function show()
    {
        if (empty($this->page) || !$this->getCmsService()->hasPage($this->page)) {
            return Result::ERROR;
        }
        
        try {
            $this->config = $this->getPageConfig();
            if ($this->config['active'] !== true) {
                throw new \FwkWWW\Exceptions\PageNotFound($this->page);
            }
        } catch(\Exception $exp) {
            echo $exp;
            return Result::ERROR;
        }
        
        return $this->getCmsService()->render($this->page, $this->getContext(), array(
            '_helper'   => $this->getServices()->get('viewHelper'),
            'query'     => $this->getContext()->getRequest()->query->all(),
            'request'   => $this->getContext()->getRequest()->request->all()
        ));
    }
    
    protected function getPageConfig()
    {
        if (isset($this->config)) {
            return $this->config;
        }
        
        $this->config = $this->getCmsService()->getPageConfig($this->page);
        
        return $this->config;
    }
    
    /**
     * 
     * @return \FwkWWW\CmsService
     */
    protected function getCmsService()
    {
        return $this->getServices()->get('cms');
    }
}