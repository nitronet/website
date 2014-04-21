<?php
namespace Nitronet;

use FwkWWW\DataSource;
use Fwk\Xml\Map;
use Fwk\Xml\Path;
use Fwk\Xml\XmlFile;

class FwkPackagesDataSource implements DataSource
{
    protected $xmlFile;
    protected $packages;
    
    public function __construct($xmlFile)
    {
        $this->xmlFile = $xmlFile;
    }
    
    public function one($name)
    {
        $this->load();
        
        if (!isset($this->packages[$name])) {
            throw new \RuntimeException('Package not found');
        }
        
        return $this->packages[$name];
    }
    
    public function packages()
    {
        $this->load();
        
        return $this->packages;
    }
    
    protected function load()
    {
        if (isset($this->packages)) {
            return;
        }
        
        $file = new XmlFile($this->xmlFile);
        $res = self::xmlMapFactory()->execute($file);
        
        $this->packages = $res['packages'];
    }
    
    /**
     * 
     * @return Map
     */
    protected static function xmlMapFactory()
    {
        $map = new Map();
        $map->add(
            Path::factory('/packages/package', 'packages', array())
                ->loop(true, '@id')
                ->addChildren(Path::factory('name', 'name'))
                ->addChildren(Path::factory('description', 'description'))
                ->addChildren(Path::factory('repository', 'repository'))
                ->addChildren(Path::factory('intro', 'intro'))
                ->addChildren(Path::factory('docs', 'docs'))
                ->addChildren(Path::factory('icon', 'icon'))
        );
        
        return $map;
    }
}