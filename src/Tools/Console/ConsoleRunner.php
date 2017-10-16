<?php
namespace SRESTO\Tools\Console;
use Symfony\Component\Console\Application;
use SRESTO\Tools\Console\Commands\GeneratorCommand;
use SRESTO\Tools\Console\Commands\ResourceCommand;
use SRESTO\Tools\Console\Commands\ProcessorCommand;
use SRESTO\Tools\Console\Commands\REPLCommand;
use SRESTO\Tools\Console\Commands\RouteCacheCommand;

class ConsoleRunner{
    public static function createApplication(){
        $app = new Application("SRESTO Console Runner","1.0.0");
        $app->add(new GeneratorCommand());
        $app->add(new ResourceCommand());
        $app->add(new ProcessorCommand());
        $app->add(new REPLCommand());
        $app->add(new RouteCacheCommand());
        return $app;
    }
}