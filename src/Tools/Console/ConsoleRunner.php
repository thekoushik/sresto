<?php
namespace SRESTO\Tools\Console;
use Symfony\Component\Console\Application;
use SRESTO\Tools\Console\Commands\GeneratorCommand;

class ConsoleRunner{
    public static function createApplication(){
        $app = new Application("SRESTO Console Runner","1.0.0");
        $app->add(new GeneratorCommand());
        return $app;
    }
}