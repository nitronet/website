<?php
namespace Nitronet\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Fwk\Core\ServicesAware;
use Fwk\Di\Container;

class FwkFetchCommand extends Command implements ServicesAware
{
    protected $services;

    protected function configure()
    {
        $this->setDescription('Fetches Fwk Package(s) and build documenation');
        $this->setName('fwk:fetch');
        $this->addArgument(
            'packages',
            InputArgument::OPTIONAL,
            'Packages to be fetched, separated by coma. All packages if not specified'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pkgs = $input->getArgument('packages');

        if (!empty($pkgs)) {
            $pkgs = (strpos($pkgs, ',') > 0 ? explode(',', $pkgs) : array($pkgs));
        } else {
            $pkgs = false;
        }

        $cms = $this->getServices()->get('cms');
        $cfg = $cms->getPageConfig('fwk');

        $dsf = new \FwkWWW\DataSourceFactory($this->getServices());
        $dsf->load((isset($cfg['datasources']) ? $cfg['datasources'] : array()));

        $pkgDs  = $dsf->factory('packages');
        $all    = $pkgDs->packages();
        $buildDir = $this->getServices()->getProperty('fwk.build.dir');

        if (!is_dir($buildDir)) {
            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                $output->write('Directory '. $buildDir .' not found. Creating...', true);
            }
            mkdir($buildDir);
        }

        foreach ($all as $pkgName => $data) {
            if ($pkgs !== false && in_array($pkgName, $pkgs)) {
                if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                    $output->write('Skipping '. $pkgName .'.', true);
                }
                continue;
            }
            $output->write('Fetching informations about <info>'. $pkgName .'</info> ...', true);
        }
    }

    /**
     *
     * @return Container
     */
    public function getServices()
    {
        return $this->services;
    }

    public function setServices(Container $container)
    {
        $this->services = $container;
    }
}
