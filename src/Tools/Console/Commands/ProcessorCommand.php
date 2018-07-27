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

class ProcessorCommand extends Command{
    protected function configure(){
        $this->setName("make:processor")
             ->setDescription("Generates processor.")
             ->addArgument('class', InputArgument::REQUIRED, 'Processor class name');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $io = new SymfonyStyle($input, $output);
        $clazz = $input->getArgument('class');
        $filename=Configuration::get("processor_package_path").DIRECTORY_SEPARATOR.$clazz.".php";
        if(file_exists($filename)){
            $io->error("File '$filename' already exists.");
            return 0;
        }
        $path=strToSnakeCase($clazz);
        $template=<<<'EOT'
<?php
namespace API\Processors;
/**
 * @RequestMapping(path="{path}")
 */
class {clazz} {
    /** @RequestMapping(method="GET") */
    public function process($req,$res){
        $res->message("{clazz} works!");
    }
}
EOT;
        $vars=['path'=>$path,'clazz'=>$clazz];
        file_put_contents($filename,CoreUtil::parseTemplateString($template,$vars));
        $output->writeln("Processor $clazz added.");
    }
}