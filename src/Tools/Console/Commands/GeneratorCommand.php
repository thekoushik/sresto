<?php
namespace SRESTO\Tools\Console\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;

//use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Console\Style\SymfonyStyle;

class GeneratorCommand extends Command{
    protected function configure(){
        $this->setName("cool")
                ->setDescription("Shows all features that cli can do");
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $table = new Table($output);
        $table->setHeaders(array('ISBN', 'Title', 'Author'))
              ->setRows(array(
                array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
                array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
                new TableSeparator(),
                array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
                array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
                new TableSeparator(),
                array(new TableCell('This value spans 3 columns.', array('colspan' => 3))),
            ));
        $table->render();

        /*$command = $this->getApplication()->find('make:other');
        $arguments = array(
            'Password' => 'hello',
            //'--yell'  => true,
        );
        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);
        */
        $io = new SymfonyStyle($input, $output);

        $io->confirm('Wanna see more cool stuff?');

        $io->title('Some Title');
        $io->section('Texting');
        $io->text('Single String');
        $io->text(array(
            'String array1',
            'String array2',
            'String array3',
        ));
        $io->section('Listing');
        $io->listing(array(
            'Element #1 Lorem ipsum dolor sit amet',
            'Element #2 Lorem ipsum dolor sit amet',
            'Element #3 Lorem ipsum dolor sit amet',
        ));
        $io->section('Table');
        $io->table(
            array('Header 1', 'Header 2'),
            array(
                array('Cell 1-1', 'Cell 1-2'),
                array('Cell 2-1', 'Cell 2-2'),
                array('Cell 3-1', 'Cell 3-2'),
            )
        );
        $io->confirm('Wanna see more cool stuff?');
        $io->section('3 New lines');
        $io->newLine(3);
        $io->note('use simple strings for short notes');
        $io->section('consider using arrays when displaying long notes');
        $io->note(array(
            'Lorem ipsum dolor sit amet',
            'Consectetur adipiscing elit',
            'Aenean sit amet arcu vitae sem faucibus porta',
        ));
        $io->caution('use simple strings for short caution message');
        $io->caution(array(
            'Lorem ipsum dolor sit amet',
            'Consectetur adipiscing elit',
            'Aenean sit amet arcu vitae sem faucibus porta',
        ));
        $io->success('Lorem ipsum dolor sit amet');
        $io->warning('Lorem ipsum dolor sit amet');
        $io->error('Lorem ipsum dolor sit amet');

        $io->progressStart(100);
        for($i=0;$i<10;$i++){
            usleep(100000);//100 milisecond
            $io->progressAdvance(5);
        }
        sleep(1);
        $io->progressFinish();

        if($io->confirm('Restart the web server?',true)){
            $io->success("Done");
        }
        $output->writeln(getcwd());
    }
}