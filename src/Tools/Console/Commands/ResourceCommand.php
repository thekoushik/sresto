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
use SRESTO\Utils\Helper;

class ResourceCommand extends Command{
    /*private $entityManager;
    public function  __construct(EntityManager $em){
        parent::__construct();
        $this->entityManager=$em;
    }*/
    protected function configure(){
        $this->setName("make:resource")
             ->setDescription("Generates resource by asking questions.");
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the name of the class: ');
        $clazz = trim($helper->ask($input, $output, $question));
        if(empty($clazz)){
            $io->error("Class name is required");
            return;
        }

        $tableName=Helper::strToSnakeCase($clazz);
        if(substr($tableName,-1,1)!=="s") $tableName.="s";
        
        $body=[];
        $body[]="    /**\n     * @Id\n     * @Column(type=\"integer\")\n     * @GeneratedValue\n     */\n    protected ".'$id'.";";
        $body2=[];
        $body2[]='    public function getId(){return $this->id;}';
        $body2[]='    public function setId($id){$this->id=$id;}';
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
            $body[]="    /** @Column($type) */\n    protected $".$prop.";";
            $body2[]='    public function get'.Helper::strToPascalCase($prop).'(){return $this->'.$prop.';}';
            $body2[]='    public function set'.Helper::strToPascalCase($prop).'($arg){$this->'.$prop.'=$arg;}';
            /*$question = new ConfirmationQuestion('Continue with this action?[y/n] (defaults to no) ', false);
            if ($helper->ask($input, $output, $question)) {
                $output->writeln('Accepted');
            }*/
        }
        file_put_contents(Configuration::get("resource_package_path").DIRECTORY_SEPARATOR.$clazz.".php",
        "<?php\nnamespace API\Resources;\nuse SRESTO\Storage\Resource;\n/**\n * @Entity\n * @Table(name=\"$tableName\")\n */\nclass $clazz extends Resource{\n".implode("\n",$body)."\n".implode("\n",$body2)."\n}");
        $output->writeln("Resource ".$clazz." added.");
        $io->note("run 'php sresto make:schema' to create table in the database.");
    }
}