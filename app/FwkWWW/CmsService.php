<?php
namespace FwkWWW;

use Symfony\Component\Yaml\Yaml;
use FwkWWW\InvalidConfigFile;

class CmsService
{
    const SITE_CONFIG_FILE = 'site.yml';
    const PAGE_CONFIG_FILE = 'page.yml';
    
    protected $sitePath;
    protected $configFile;
    protected $pageConfigFile;
    
    private $config;
    
    public function __construct($sitePath, 
        $configFile = null, $pageConfigFile = null
    ) {
        $this->sitePath = $sitePath;
        $this->configFile = (is_null($configFile) ? self::SITE_CONFIG_FILE : $configFile);
        $this->pageConfigFile = (is_null($pageConfigFile) ? self::PAGE_CONFIG_FILE : $pageConfigFile);
    }
    
    /**
     * Parses and return the site.yml configuration file
     * 
     * @return \stdClass
     * @throws InvalidConfigFile
     */
    public function getSiteConfig()
    {
        if (isset($this->config)) {
            return $this->config;
        }
        
        $file = $this->sitePath . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            throw new InvalidConfigFile($this->configFile);
        }
        
        try {
            $cfg = Yaml::parse($file);
            $this->config = $this->arrayToObject(
                $this->mergeConfig($cfg)
            );
        } catch(\Exception $exp) {
            throw new InvalidConfigFile($this->configFile, null, $exp);
        }
        
        return $this->config;
    }
    
    protected function arrayToObject($array)
    {
        if (is_array($array)) {
            return (object)array_map(array($this, __FUNCTION__), $array);
        }
        else {
            return $array;
        }
    }
    
    /**
     * Default configuration settings
     * 
     * @return array
     */
    protected function defaultConfigArray()
    {
        return array(
            'name'          => 'unknown',
            'namespace'     => 'unknown',
            'title'         => null,
            'description'   => null,
            'homepage'      => 'index',
            'errorpage'     => 'error',
            'directories' => array(
                'pages'     => './pages',
                'sources'   => './php',
                'assets'    => './assets',
                'forms'     => './forms',
                'layouts'   => './layouts',
                'cache'     => '../cache'
            ),
            'page_suffix'   => null,
            'favicon'       => null,
            'default_layout'    => null,
            'domain'        => array(
                'name'      => null,
                'force'     => false
            ),
            'config'        => array(),
            'page_config'   => array(
                'template'  => null,
                'template_ajax'     => null,
                'template_widget'   => null,
                'active'    => true,
                'redirect'  => false,
                'http_code' => 200,
                'cache'     => array(
                    'enable'    => false,
                    'lifetime'  => 3600
                ),
                'config'    => array(),
                'listeners' => array(),
                'defaults'  => array()
            )
        );
    }
    
    protected function mergeConfig(array $userConf)
    {
        $source = $this->defaultConfigArray();
        $final  = array();
        foreach ($source as $key => $value) {
            if (!is_array($value)) {
                $final[$key] = (isset($userConf[$key]) ? $userConf[$key] : $value);
                continue;
            } 
            
            $final[$key] = array_merge($value, (isset($userConf[$key]) ? $userConf[$key] : $value));
        }
        
        return $final;
    }
}