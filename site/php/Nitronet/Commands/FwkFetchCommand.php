<?php
namespace Nitronet\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Fwk\Core\ServicesAware;
use Fwk\Di\Container;
use Symfony\Component\Process\Process;

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
                $output->writeln('Directory '. $buildDir .' not found. Creating...', true);
            }
            if (!mkdir($buildDir)) {
                throw new \Exception('Unable to make build directory: '. $buildDir);
            }
        }

        $phpDocBin = $this->getServices()->getProperty('phpdoc.bin');
        foreach ($all as $pkgName => $data) {
            if ($pkgs !== false && !in_array($pkgName, $pkgs)) {
                if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                    $output->writeln('Skipping '. $pkgName .'.', true);
                }
                continue;
            }
            $output->writeln('Building package <info>'. $pkgName .'</info> ...', true);
            $this->fetchOrUpdatePackage($pkgName, $data, $buildDir, $output);
            $this->buildApiDoc($pkgName, $data, $buildDir, $phpDocBin, $output);
            $this->buildDocumentation($pkgName, $data, $buildDir, $output);
        }
    }

    protected function buildDocumentation($pkg, $data, $buildDir, OutputInterface $output)
    {
        if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('- Building Documentation ...');
        }


    }

    protected function buildApiDoc($pkg, $data, $buildDir, $phpDocBin, OutputInterface $output)
    {
        if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('- Building API documentation ...');
        }

        $proc = $this->proc(sprintf('%s -d . -t ./build/apidoc --template="xml" --ignore="*vendor*,*docs*,*Tests*"', $phpDocBin), $buildDir . DIRECTORY_SEPARATOR . $pkg, $output, 200);
        if (!$proc->isSuccessful()) {
            throw new \Exception('Unable to build API documentation: '. $proc->getErrorOutput());
        }
    }

    protected function fetchOrUpdatePackage($pkg, $data, $buildDir, OutputInterface $output)
    {
        if (!is_dir($buildDir . DIRECTORY_SEPARATOR . $pkg)) {
            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln('- Package previous installation not found. Cloning repository ...');
            }
            $proc = $this->proc('git clone '. $data['repository'], $buildDir, $output);
            if (!$proc->isSuccessful()) {
                throw new \Exception('Unable to clone package repository: '. $pkg .'/'. $data['repository'] .': '. $proc->getErrorOutput());
            }
        } else {
            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln('- Previous installation found. Updating ...');
            }
            $proc = $this->proc('git pull -u origin master -ff', $buildDir . DIRECTORY_SEPARATOR . $pkg, $output);
            if (!$proc->isSuccessful()) {
                throw new \Exception('Unable to update package repository: '. $pkg .'/'. $data['repository'] .': '. $proc->getErrorOutput());
            }
        }
    }

    /**
     * @param $cmd
     * @param $cwd
     * @param OutputInterface $output
     * @param int $timeout
     * @return Process
     */
    private function proc($cmd, $cwd, OutputInterface $output, $timeout = 60)
    {
        $proc = new Process($cmd, $cwd);
        if (null !== $timeout) {
            $proc->setTimeout($timeout);
        }
        $proc->run(function ($type, $buffer) use ($output) {
            if ('err' === $type) {
                $output->write('<error>ERROR</error>: '. $buffer);
            } elseif ($output->getVerbosity() === OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $output->write('<info>INFO</info>: '. $buffer);
            }
        });

        return $proc;
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
