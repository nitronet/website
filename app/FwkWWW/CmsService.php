<?php
namespace FwkWWW;

use Symfony\Component\Yaml\Yaml;
use FwkWWW\Exceptions\InvalidConfigFile;
use FwkWWW\Exceptions\PageNotFound;

class CmsService
{
    const SITE_CONFIG_FILE  = 'site.yml';
    const PAGES_EXTENSION   = 'twig';
    
    /**
     * Path utility
     * @var PathUtils
     */
    protected $path;
    
    protected $configFile;
    
    private $config;
    
    public function __construct($sitePath, $configFile = null)
    {
        $this->path         = new PathUtils($sitePath);
        $this->configFile   = (is_null($configFile) ? self::SITE_CONFIG_FILE : $configFile);
    }
    
    public function hasPage($pageName)
    {
        $cfg    = $this->getSiteConfig();
        try {
            $path   = $this->path->calculate(
                array(
                    $cfg['directories']['pages'], 
                    strtolower($pageName) .'.'. self::PAGES_EXTENSION
                )
            );
        } catch(\FwkWWW\Exception $exp) {
            return false;
        }
        
        return is_file($path);
    }
    
    public function getPageConfig($pageName)
    {
        if (!$this->hasPage($pageName)) {
            throw new PageNotFound($pageName);
        }
        
        $cfg    = $this->getSiteConfig();
        try {
            $path   = $this->path->calculate(
                array(
                    $cfg['directories']['config'], 
                    strtolower($pageName) .'.yml'
                )
            );
        } catch(\FwkWWW\Exception $exp) {
            return $cfg['page_config'];
        }
        
        try {
            $pcfg = $this->mergeConfig(Yaml::parse($path), $cfg['page_config']);
        } catch(\Exception $exp) {
            throw new InvalidConfigFile($path, null, $exp);
        }

        return $pcfg;
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
        
        try {
            $cfg = Yaml::parse($this->path->calculate(array($this->configFile)));
            $this->config = $this->mergeConfig($cfg);
        } catch(\Exception $exp) {
            throw new InvalidConfigFile($this->configFile, null, $exp);
        }
        
        return $this->config;
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
                'cache'     => '../cache',
                'config'    => '../config'
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
    
    protected function mergeConfig(array $userConf, $source = null)
    {
        if (null === $source) {
            $source = $this->defaultConfigArray();
        }
        
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