<?php
namespace Nitronet;

use FwkWWW\DataSource;
use Fwk\Xml\XmlFile;
use Fwk\Xml\Map;
use Fwk\Xml\Path;

class FwkApiDocDataSource implements DataSource
{
    protected $xmlPath;
    
    protected $classes;
    
    public function __construct($xmlPath)
    {
        $this->xmlPath = $xmlPath;
    }
    
    public function classDoc($className)
    {
        $this->load();
        
        $key = '\\' . str_replace('/', '\\', $className);
        
        if (!isset($this->classes[$key])) {
            throw new \InvalidArgumentException('unknown class: '. $className);
        }
        
        return $this->classes[$key];
    }
    
    public function classes($package)
    {
        $this->load();
        
        return $this->classes;
    }
    
    protected function load()
    {
        if (isset($this->classes)) {
            return;
        }
        
        $file = new XmlFile($this->xmlPath);
        $res = self::xmlMapFactory()->execute($file);
        
        ksort($res['classes'], SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($res['classes'] as &$data) {
            ksort($data['methods'], SORT_NATURAL);
        }
        $this->classes = $res['classes'];
    }
    
    /**
     * 
     * @return Map
     */
    protected static function xmlMapFactory()
    {
        $map = new Map();
        $map->add(
            Path::factory('/project/file/class', 'classes', array())
                ->loop(true, 'full_name')
                ->attribute('abstract')
                ->attribute('final')
                ->addChildren(Path::factory('name', 'name'))
                ->addChildren(Path::factory('extends', 'extends', null))
                ->addChildren(Path::factory('implements', 'implements', array())->loop(true))
                ->addChildren(self::docBlockPathFactory())
                ->addChildren(
                    Path::factory('constant', 'constants', array())
                    ->loop(true, 'name')
                    ->addChildren(Path::factory('full_name', 'full_name'))
                    ->addChildren(Path::factory('value', 'value'))
                    ->addChildren(self::docBlockPathFactory())
                )
                ->addChildren(
                    Path::factory('property', 'properties', array())
                    ->loop(true, 'name')
                    ->attribute('visibility')
                    ->attribute('static')
                    ->addChildren(Path::factory('full_name', 'full_name'))
                    ->addChildren(Path::factory('default', 'default'))
                    ->addChildren(self::docBlockPathFactory())
                )
                ->addChildren(
                    Path::factory('method', 'methods', array())
                    ->loop(true, 'name')
                    ->attribute('final')
                    ->attribute('abstract')
                    ->attribute('visibility')
                    ->attribute('static')
                    ->addChildren(
                        Path::factory('argument', 'arguments', array())
                        ->loop(true)
                        ->addChildren(Path::factory('name', 'name'))
                        ->addChildren(Path::factory('default', 'default'))
                        ->addChildren(Path::factory('type', 'type'))
                    )
                    ->addChildren(self::docBlockPathFactory())
                )
        );
        
        return $map;
    }
    
    private static function docBlockPathFactory()
    {
        return Path::factory('docblock', 'docblock')
            ->addChildren(
                Path::factory('description', 'description', null)
            )
            ->addChildren(
                Path::factory('long-description', 'longDescription', null)
            )
            ->addChildren(
                Path::factory('tag', 'tags', array())
                ->loop(true, '@name')
                ->attribute('description')
                ->attribute('link')
                ->attribute('type')
            )
        ;
    }
}