<?php
namespace FwkWWW\Controllers;

use Fwk\Core\Action\Result;
use Fwk\Core\Action\Controller;
use Fwk\Core\Preparable;
use FwkWWW\Exceptions\InvalidDataSource;

class PageView extends Controller implements Preparable
{
    public $page;
    
    protected $config;
   
    protected $datasources = array();
    
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
        
        try {
            $this->datasources = $this->loadDataSources();
        } catch (\Exception $exp) {
            echo $exp;
            return Result::ERROR;
        }
        
        return $this->getCmsService()->render($this->page, $this->getContext(), array_merge(array(
            '_helper'   => $this->getServices()->get('viewHelper'),
            'query'     => $this->getContext()->getRequest()->query->all(),
            'request'   => $this->getContext()->getRequest()->request->all()
        ), $this->datasources));
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
    
    /**
     * 
     * @return array
     */
    protected function loadDataSources()
    {
        if (!isset($this->config['datasources'])) {
            return;
        }
        
        $this->getCmsService()->initClassLoader();
        $container  = $this->getServices();
        $return     = array();
        foreach ($this->config['datasources'] as $sourceName => $infos) {
            $className  = (isset($infos['class']) ? $infos['class'] : null);
            $service    = (isset($infos['service']) ? $infos['service'] : null);
            
            if (empty($className) && empty($service)) {
                throw new InvalidDataSource(
                    $sourceName, 
                    'You should specify a class or a service name, none provided'
                );
            }
            
            if (!empty($service)) {
                try {
                    $return[$sourceName] = $container->get($service);
                } catch(Fwk\Di\Exception $exp) {
                    throw new InvalidDataSource(
                        $sourceName, 
                        'Invalid service', 
                        null, 
                        $exp
                    );
                }
                continue;
            }
            
            $definition = new \Fwk\Di\ClassDefinition($className, (isset($infos['constructor']) ? $infos['constructor'] : array()));
            $container->set('_cms.datasource.'. $sourceName, $definition, true);
            try {
                $return[$sourceName] = $container->get('_cms.datasource.'. $sourceName);
            } catch(Fwk\Di\Exception $exp) {
                throw new InvalidDataSource(
                    $sourceName, 
                    'Invalid definition', 
                    null, 
                    $exp
                );
            }
        }
        
        return $return;
    }
}