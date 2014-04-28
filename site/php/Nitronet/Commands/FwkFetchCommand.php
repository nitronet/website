<?php
namespace Nitronet\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FwkFetchCommand extends Command
{
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
        
        if (null !== $pkgs) {
            $pkgs = (strpos($pkgs, ',') > 0 ? explode(',', $pkgs) : array($pkgs));
        }
    }
}