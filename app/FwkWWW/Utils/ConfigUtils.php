<?php
namespace FwkWWW\Utils;


class ConfigUtils
{
    /**
     * Default configuration settings
     * 
     * @return array
     */
    public static function defaultConfigArray()
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
                    'public'    => true,
                    'enable'    => false,
                    'lifetime'  => 3600
                ),
                'config'    => array(),
                'listeners' => array(),
                'defaults'  => array()
            )
        );
    }
    
    public static function merge(array $userConf, $source = null)
    {
        if (null === $source) {
            $source = self::defaultConfigArray();
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