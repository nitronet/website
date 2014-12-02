<?php
namespace FwkWWW\Providers\Page;

use FwkWWW\Providers\PageProvider;
use Fwk\Core\Context;
use FwkWWW\Utils\PathUtils;
use FwkWWW\Exceptions\PageNotFound;
use Symfony\Component\Yaml\Yaml;
use FwkWWW\Exceptions\InvalidConfigFile;
use FwkWWW\Utils\ConfigUtils;
use FwkWWW\Exception;

class TwigFilesystemProvider implements PageProvider
{
    const PAGES_EXTENSION   = '.twig';
    
    protected $twig;
    protected $path;
    
    public function __construct($sitePath, \Twig_Environment $twig)
    {
        $this->twig     = $twig;
        $this->path     = new PathUtils($sitePath);
    }
    
    public function has($pageName, array $config)
    {
        try {
            $path = $this->path->calculate(
                array(
                    $config['directories']['pages'], 
                    strtolower($pageName) . self::PAGES_EXTENSION
                )
            );
        } catch(Exception $exp) {
            return false;
        }
        
        return is_file($path);
    }
    
    public function getConfig($pageName, array $config)
    {
        if (!$this->has($pageName, $config)) {
            throw new PageNotFound($pageName);
        }
        
        try {
            $path = $this->path->calculate(
                array(
                    $config['directories']['config'], 
                    strtolower($pageName) .'.yml'
                )
            );
        } catch(Exception $exp) {
            return $config['page_config'];
        }
        
        try {
            $pcfg = ConfigUtils::merge(Yaml::parse(file_get_contents($path)), $config['page_config']);
        } catch(\Exception $exp) {
            throw new InvalidConfigFile($path, null, $exp);
        }

        return $pcfg;
    }
    
    public function render($pageName, Context $context, array $config, array $params = array())
    {
        $template   = $this->calculateTemplate($pageName, $context, $config);
        $pagesPath  = $this->path->calculate(array($config['directories']['pages']));
        $path       = str_replace($pagesPath, '', $template);
        
        return $this->twig->render($path, $params);
    }
    
    public function getLastModified($pageName, Context $context, array $config)
    {
        try {
            $configFile = $this->path->calculate(
                array(
                    $config['directories']['config'], 
                    strtolower($pageName) .'.yml'
                )
            );
        } catch(Exception $exp) {
            $configFile = null;
        }

        $template   = $this->calculateTemplate($pageName, $context, $config);
        $tplTime    = filemtime($template);
        $now        = new \DateTime();
        
        if (null !== $configFile) {
            $cfgTime = filemtime($configFile);
            if ($cfgTime > $tplTime) {
                return $tplTime;
            } else {
                return $cfgTime;
            }
        } 
        
        return $tplTime;
    }
    
    protected function calculateTemplate($pageName, Context $context, array $config)
    {
        $cfg = $this->getConfig($pageName, $config);
        
        // widget
        if ($context->hasParent()) {
            $tpl = ($cfg['template_widget'] != null ? $cfg['template_widget'] . self::PAGES_EXTENSION : $pageName . self::PAGES_EXTENSION);
        } elseif ($context->getRequest()->isXmlHttpRequest()) {
            $tpl = ($cfg['template_ajax'] != null ? $cfg['template_ajax'] . self::PAGES_EXTENSION : $pageName . self::PAGES_EXTENSION);
        } else {
            $tpl = $pageName . self::PAGES_EXTENSION;
        }
        
        $template = $this->path->calculate(
            array(
                $config['directories']['pages'], 
                $tpl
            )
        );
        
        return $template;
    }
}