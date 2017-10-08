<?php
namespace SRESTO\Tools\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;

class REPLCommand extends Command{
    protected function configure(){
        $this->setName("repl")
             ->setDescription("Simple REPL in php.");
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $io = new SymfonyStyle($input, $output);
        $output->writeln('PHP REPL');
        $output->writeln('By: Koushik Seal');

        $helper = $this->getHelper('question');
        while(1){
            $question = new Question('>>> ');
            $code = trim($helper->ask($input, $output, $question));
            if(empty($code)){
                $output->writeln("Bye.");
                return;
            }
            try{
                $result=eval($code);
                $output->writeln("\n<<< ".$result);
            }catch(\Exception $ex){
                $io->error($ex->getMessage());
            }
        }
    }
}