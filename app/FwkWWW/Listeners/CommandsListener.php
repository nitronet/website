<?php
namespace FwkWWW\Listeners;

use Fwk\Core\Events\DispatchEvent;
use Fwk\Di\ClassDefinition;
use Fwk\Di\Container;
use Fwk\Core\ContextAware;
use Fwk\Core\ServicesAware;
use Fwk\Core\Preparable;

class CommandsListener
{
    /**
     * @var string
     */
    protected $consoleService;
    
    protected $cmsService;
    
    /**
     * Constructor
     * 
     */
    public function __construct($consoleServiceName, $cmsServiceName)
    {
        $this->consoleService  = $consoleServiceName;
        $this->cmsService       = $cmsServiceName;
    }
    
    public function onDispatch(DispatchEvent $event)
    {
        if (!$this->isCli()) {
            return;
        }
        
        $container  = $event->getApplication()->getServices();
        $cmsService = $event->getApplication()
                            ->getServices()
                            ->get($this->cmsService);
        $cfg        = $cmsService->getSiteConfig();
        if (!isset($cfg['commands']) || !is_array($cfg['commands'])) {
            return;
        }
        
        $app        = $this->getConsoleApplication($container);
        $cmsService->initClassLoader();
        
        foreach ($cfg['commands'] as $command) {
            $def = new ClassDefinition($command);
            $cmd = $def->invoke($container);

            if ($cmd instanceof ContextAware) {
                $cmd->setContext($event->getContext());
            }

            if ($cmd instanceof ServicesAware) {
                $cmd->setServices($container);
            }

            if ($cmd instanceof Preparable) {
                call_user_func_array(array($cmd, Preparable::PREPARE_METHOD));
            }
            
            $app->add($cmd);
        }
    }
    
    protected function isCli()
    {
        return (php_sapi_name() === "cli");
    }
    
    /**
     * 
     * @param \Fwk\Di\Container $container
     * 
     * @return \Symfony\Component\Console\Application
     */
    protected function getConsoleApplication(Container $container)
    {
        return $container->get($this->consoleService);
    }
}