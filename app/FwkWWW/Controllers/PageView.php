<?php
namespace FwkWWW\Controllers;

use Fwk\Core\Action\Result;
use Fwk\Core\Action\Controller;
use Fwk\Core\Preparable;

class PageView extends Controller implements Preparable
{
    public $page;
    
    protected $template;
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
        if (empty($this->page)) {
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
        
        // calculate the template to-be-rendered for this page
        $this->template = $this->calculateTemplate();
        
        return 'cms:page';
    }
    
    protected function getPageConfig()
    {
        if (isset($this->config)) {
            return $this->config;
        }
        
        $this->config = $this->getCmsService()->getPageConfig($this->page);
        
        return $this->config;
    }
    
    protected function calculateTemplate()
    {
        $cfg        = $this->getPageConfig();
        $context    = $this->getContext();
        
        // widget
        if ($context->hasParent()) {
            return ($cfg['template_widget'] != null ? $cfg['template_widget'] : $this->page .'.twig');
        } elseif ($context->getRequest()->isXmlHttpRequest()) {
            return ($cfg['template_ajax'] != null ? $cfg['template_ajax'] : $this->page .'.twig');
        } 
        
        return $this->page .'.twig';
    }
    
    /**
     * 
     * @return \FwkWWW\CmsService
     */
    protected function getCmsService()
    {
        return $this->getServices()->get('cms');
    }
    
    public function getTemplate()
    {
        return $this->template;
    }
}