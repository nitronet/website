<?php
namespace FwkWWW;

use Fwk\Di\Container;
use Fwk\Di\ClassDefinition;
use Fwk\Di\Reference;
use FwkWWW\Exceptions\InvalidDataSource;

class DataSourceFactory
{
    /**
     * @var Container
     */
    protected $services;
    
    protected $sources = array();
    
    public function __construct(Container $services)
    {
        $this->services = $services;
    }
    
    /**
     * 
     * @return array
     */
    public function factoryAll()
    {
        $result = array();
        foreach ($this->sources as $sourceName => $def) {
            $result[$sourceName] = $this->services->get($def);
        }
        
        return $result;
    }
    
    /**
     * 
     * @param string $name
     * 
     * @return DataSource
     */
    public function factory($name)
    {
        return $this->services->get('cms.ds.'. $name);
    }
    
    public function registerClass($name, $className, 
        array $constructorArgs = array()
    ) {
        $definition = new ClassDefinition($className, $constructorArgs);
        $this->services->set('cms.ds.'. $name, $definition, true);
        $this->sources[$name] = 'cms.ds.'. $name;
    }
    
    public function registerService($name, $serviceName)
    {
        $this->services->set('cms.ds.'. $name, new Reference($serviceName));
        $this->sources[$name] = 'cms.ds.'. $name;
    }
    
    public function load(array $config)
    {
        foreach ($config as $sourceName => $infos) {
            $className  = (isset($infos['class']) ? $infos['class'] : null);
            $service    = (isset($infos['service']) ? $infos['service'] : null);
            
            if (empty($className) && empty($service)) {
                throw new InvalidDataSource(
                    $sourceName, 
                    'You should specify a class or a service name, none provided'
                );
            }
            
            if (!empty($service)) {
                $this->registerService($sourceName, $service);
                continue;
            }
            
            $this->registerClass(
                $sourceName, 
                $className, 
                (isset($infos['constructor']) ? $infos['constructor'] : array())
            );
        }
    }
}