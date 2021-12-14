<?php
namespace App\Command;

use App\Service\CatalogImport;
use Symfony\Component\Console\Command\Command;
use App\Repository\CatalogRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCatalogCommand extends Command
{
    //The name of the command (the part after "bin/console")
    protected static $defaultName = 'app:products:import';

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;
    
    /**
     * @var CatalogImport
     */
    private $catalogImport;

    public function __construct(CatalogImport $catalogImport, CatalogRepository $catalogRepository)
    {
        $this->catalogImport = $catalogImport;
        $this->catalogRepository = $catalogRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        //The command description shown when running "php bin/console list"
        ->setDescription('Import products from one or many JSON file/s and persist them to the database.')

        //The command help shown when running the command with the "--help" option
        ->setHelp('This command allows you to import products from one or many JSON file/s and persist them to the database.')
        
        ->addArgument('catalog_path', InputArgument::OPTIONAL, 'The path of the catalog file to be imported.')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //If the user especified a catalog we handle only that catalog otherwise handle all the submitted catalogs
        //If the import service return errors show it to the user
        $catalog_path = $input->getArgument('catalog_path'); 
        if($catalog_path){
            $catalog = $this->catalogRepository->findOneBy(['filePath' => $catalog_path]);
            if($catalog && $catalog->getState() == 'submitted') {
                $result =$this->catalogImport->handleSingle($catalog);
                if(!$result) {
                    $output->writeln([
                        'Error. Something went wrong with the import process.',
                    ]);      
                    return Command::FAILURE;
                }else if($result>0){
                    $output->writeln([
                        'Success. The import process finished with some errors.',
                    ]);
                    $output->writeln([
                        'The imported catalog: has: '.$result.'products that couldnt be imported',
                    ]);   
                }
            }else{
                $output->writeln([
                    'Error. The selected Catalog has been already imported or doesnt exist.',
                ]);            
                return Command::INVALID;
            }
        }else {
            //If the service returns an array iterate it so it shows how many products were not imported.
            $result = $this->catalogImport->handleMultiple(); 
            if(!$result) {
                $output->writeln([
                    'Error. Something went wrong with the import process.',
                ]);
                return Command::FAILURE;
            } else if(count($result)>0){
                $output->writeln([
                    'Success. The import process finished with some errors.',
                ]);
                foreach($result as $catalog => $errors){
                    $output->writeln([
                        'The following catalog: '.$catalog.' has: '.$errors.'products that couldnt be imported',
                    ]);   
                }
            }
        }
        $output->writeln([
            'Success. The import process finished successfully.',
        ]);
        return Command::SUCCESS;
    }
}