<?php
namespace SRESTO\Tools\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

class SchemaCommand extends Command{
    private $entityManager;
    public function  __construct(EntityManager $em){
        parent::__construct();
        $this->entityManager=$em;
    }
    protected function configure(){
        $this->setName("make:schema")
             ->setDescription("Generates schema from metadata.")
             ->addOption(
                'update',
                'u',
                InputOption::VALUE_NONE,
                'Updates schema from metadata.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $update=$input->getOption('update');
        $schemaTool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if($update){
            $schemaTool->updateSchema($classes);
            $output->writeln("Schema Updated.");
        }else{
            $schemaTool->createSchema($classes);
            $output->writeln("Schema Generated.");
        }
    }
}