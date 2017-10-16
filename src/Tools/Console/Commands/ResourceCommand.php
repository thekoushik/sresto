<?php
namespace SRESTO\Tools\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
//use Doctrine\ORM\EntityManager;
//use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use SRESTO\Configuration;
use SRESTO\Utils\CoreUtil;
use Symfony\Component\Console\Input\ArrayInput;

class ResourceCommand extends Command{
    /*private $entityManager;
    public function  __construct(EntityManager $em){
        parent::__construct();
        $this->entityManager=$em;
    }*/
    protected function configure(){
        $this->setName("make:resource")
             ->setDescription("Generates resource by asking questions.")
             ->addOption(
                'proc',
                null,
                InputOption::VALUE_OPTIONAL,
                'Generates a processor',
                true
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the name of the class: ');
        $clazz = trim($helper->ask($input, $output, $question));
        if(empty($clazz)){
            $io->error("Class name is required");
            return 0;
        }

        $filename=Configuration::get("resource_package_path").DIRECTORY_SEPARATOR.$clazz.".php";
        if(file_exists($filename)){
            $io->error("File '$filename' already exists.");
            return 0;
        }

        $tableName=strToSnakeCase($clazz);
        if(substr($tableName,-1,1)!=="s") $tableName.="s";
        $templatevars=['tableName'=>$tableName,'clazz'=>$clazz];
        $template=<<<'EOT'
<?php
namespace API\Resources;
use SRESTO\Storage\Resource;
/**
 * @Entity
 * @Table(name="{tableName}")
 */
class {clazz} extends Resource{
EOT;
        $body=<<<'EOT'
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
EOT;
        $body2=<<<'EOT'
    public function getId(){return $this->id;}
    public function setId($id){$this->id=$id;}
EOT;
        while(1){
            $question = new Question('Please enter property name: ');
            $prop = trim($helper->ask($input, $output, $question));
            if(empty($prop)) break;
            /*$columnName=Helper::strToSnakeCase($prop);
            $question = new Question('Please enter column name(defaults to '.$columnName.'): ',$columnName);
            $column = trim($helper->ask($input, $output, $question));*/

            $question = new ChoiceQuestion(
                'Please select a type (defaults to string)',
                array('string', 'integer'),
                0
            );
            $question->setErrorMessage('Type %s is invalid.');//Value "%s" is invalid
            $type = $helper->ask($input, $output, $question);
            switch($type){
                case "string":
                    $question = new Question('Please enter size(defaults to 255): ','255');
                    $size = trim($helper->ask($input, $output, $question));
                    $type='length='.$size.', type="'.$type.'"';
                    break;
                case "integer":
                $type='type="'.$type.'"';
                    break;
            }
            $body.="\n    /** @Column($type) */\n    protected $".$prop.";";
            $body2.="\n    public function get".strToPascalCase($prop).'(){return $this->'.$prop.';}';
            $body2.="\n    public function set".strToPascalCase($prop).'($arg){$this->'.$prop.'=$arg;}';
            /*$question = new ConfirmationQuestion('Continue with this action?[y/n] (defaults to no) ', false);
            if ($helper->ask($input, $output, $question)) {
                $output->writeln('Accepted');
            }*/
        }
        file_put_contents($filename,CoreUtil::parseTemplateString($template."\n".$body."\n".$body2."\n}\n",$templatevars));
        $output->writeln("Resource ".$clazz." added.");

        $processor=$input->getOption('proc');
        if($processor){
            if(!is_string($processor))
                $processor=$clazz."Processor";
            
            $command = $this->getApplication()
                            ->find('make:processor')
                            ->run(new ArrayInput([
                                    'command' => 'make:processor',
                                    'class'   => $processor]), $output);
        }
        $io->note("run 'php sresto make:schema' to create table in the database.");
    }
}