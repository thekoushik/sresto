<?php
namespace SRESTO\Tools\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use SRESTO\Router\BaseRouter as Router;
use SRESTO\Configuration;

class RouteCacheCommand extends Command{
    protected function configure(){
        $this->setName("cache:route")
             ->setDescription("Caches all routes.")
             ->addOption(
                'clear',
                null,
                InputOption::VALUE_NONE,
                'Clears the route cache'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $file=Configuration::get("cache_path").DIRECTORY_SEPARATOR."router";
        $clear=$input->getOption('clear');
        if($clear){
            @unlink($file);
            $output->writeln("Route cache cleared");
        }else{
            file_put_contents($file,Router::root()->createCacheFromRoutes());
            $output->writeln("Route cache generated");
        }
    }
}