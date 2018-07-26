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
use SRESTO\Configuration;
use SRESTO\Utils\CoreUtil;

class ServiceCommand extends Command{
    protected function configure(){
        $this->setName('make:service')
             ->setDescription("Generates service.")
             ->addArgument('class', InputArgument::REQUIRED, 'Service class name')
             ->addArgument('resource', InputArgument::OPTIONAL, 'Resource class name to generate CRUD')
             ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Forces resource CRUD without resource existance.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $io = new SymfonyStyle($input, $output);
        $clazz = $input->getArgument('class');
        $filename=Configuration::get('service_package_path').DIRECTORY_SEPARATOR.$clazz.'.php';
        $force=$input->getOption('force');
        if(file_exists($filename) && !$force){
            $io->error("File '$filename' already exists.");
            return 0;
        }
        $resource=$input->getArgument('resource');
        $template=<<<'EOT'
<?php
namespace API\Services;

EOT;
        if($resource){
            $resourceFull='API\\Resources\\'.$resource;
            if(!class_exists($resourceFull) && !$force){
                $io->error("Resource '$resourceFull' does not exists.");
                return 0;
            }
            //$resourceClass=Configuration::get("resource_package_path").DIRECTORY_SEPARATOR.$resource.'.php';
            $template.='use API\\Resources\\{resource};'."\n";
            $templateR=<<<'EOT'

    public function create({resource} $obj){
        $this->entityManager->persist($obj);
        $this->entityManager->flush();
        return $obj;
    }
    public function read($id){
        return $this->entityManager->find('API\\Resources\\{resource}',$id);
    }
    public function readAll(){
        $repository = $this->entityManager->getRepository('API\\Resources\\{resource}');
        return $repository->findAll();
    }
    public function update({resource} $obj){
        $mergedObj=$this->entityManager->merge($obj);
        $this->entityManager->flush();
        return $mergedObj;
    }
    public function delete({resource} $obj){
        $this->entityManager->remove($obj);
        $this->entityManager->flush();
    }
EOT;
        }else
            $templateR='';
        $template.=<<<'EOT'

class {clazz}{
    private $entityManager;
    public function __construct($em){
        $this->entityManager=$em;
    }
EOT;
        $vars=['clazz'=>$clazz,'resource'=>$resource];
        file_put_contents($filename,CoreUtil::parseTemplateString($template.$templateR."\n}",$vars));
        $output->writeln("Service $clazz added.");
    }
}