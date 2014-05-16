<?php
namespace Nitronet;

use Fwk\Cache\Manager;
use FwkWWW\DataSource;
use Fwk\Xml\Map;
use Fwk\Xml\Path;
use Fwk\Xml\XmlFile;

class FwkPackagesDataSource implements DataSource
{
    protected $xmlFile;
    protected $packages;
    protected $cache;
    
    public function __construct($xmlFile, Manager $cache = null)
    {
        $this->xmlFile = $xmlFile;
        $this->cache = $cache;
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

    public function versions($package)
    {
        $one = $this->one($package);

        $versions = $one['versions'];
        if (strpos($versions, ',') !== false) {
            return explode(',', $versions);
        }

        return array($versions);
    }
    
    protected function load()
    {
        if (isset($this->packages)) {
            return;
        }

        $fetch = function() {
            $file = new XmlFile($this->xmlFile);
            $res = self::xmlMapFactory()->execute($file);

            return $res['packages'];
        };

        if ($this->cache instanceof Manager) {
            $this->packages = $this->cache->get('fwk:packages', '1day', $fetch)->getContents();;
        }

        $this->packages = $fetch();
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
                ->addChildren(Path::factory('versions', 'versions'))
        );
        
        return $map;
    }
}