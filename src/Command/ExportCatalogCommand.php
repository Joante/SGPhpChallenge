<?php

namespace App\Command;

use App\Service\CatalogExport;
use Symfony\Component\Console\Command\Command;
use App\Repository\CatalogRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCatalogCommand extends Command
{
    //The name of the command (the part after "bin/console")
    protected static $defaultName = 'app:products:export';

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;
    
    /**
     * @var CatalogExport
     */
    private $catalogExport;

    public function __construct(CatalogExport $catalogExport, CatalogRepository $catalogRepository)
    {
        $this->catalogExport = $catalogExport;
        $this->catalogRepository = $catalogRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        //The command description shown when running "php bin/console list"
        ->setDescription('Export products from all imported catalogs and send them to the SFTP server.')

        //The command help shown when running the command with the "--help" option
        ->setHelp('This command allows you to export products from all imported catalogs and send them to the SFTP server.')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if(!$this->catalogExport->handle()) {
            $output->writeln([
                'Error. Something went wrong with the export process.',
            ]);
            return Command::FAILURE;
        }
        $output->writeln([
            'Success. The export process finished successfully.',
        ]);
        return Command::SUCCESS;
    }
}