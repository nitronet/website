<?php
namespace FwkWWW\Controllers;

use Fwk\Core\Action\Result;
use Fwk\Core\Action\Controller;

class PageView extends Controller
{
    public $page;
    
    protected $template;
    protected $config;
    
    public function show()
    {
        if (empty($this->page)) {
            return Result::ERROR;
        }
        
        $service = $this->getCmsService();
        print_r($service->getSiteConfig());
        
        return 'cms:page';
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