<?php
namespace App\MessageHandler;

use App\Entity\Catalog;
use App\Message\CatalogMessage;
use App\Repository\CatalogRepository;
use App\Service\CatalogExport;
use App\Service\CatalogImport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class CatalogMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var CatalogExport
     */
    private $catalogExport;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var WorkflowInterface
     */
    private $workflow;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CatalogMessageHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param CatalogRepository $catalogRepository
     * @param CatalogImport $catalogImport
     * @param CatalogExport $catalogExport
     * @param MessageBusInterface $bus
     * @param WorkflowInterface $catalogStateMachine
     * @param LoggerInterface|null $logger
     */
    public function __construct(EntityManagerInterface $entityManager, CatalogRepository $catalogRepository, CatalogImport $catalogImport, CatalogExport $catalogExport, MessageBusInterface $bus, WorkflowInterface $catalogStateMachine, LoggerInterface $logger = null)
    {
        $this->entityManager = $entityManager;
        $this->catalogRepository = $catalogRepository;
        $this->catalogImport = $catalogImport;
        $this->catalogExport = $catalogExport;
        $this->bus = $bus;
        $this->workflow = $catalogStateMachine;
        $this->logger = $logger;
    }

    /**
     * @param CatalogMessage $message
     */
    public function __invoke(CatalogMessage $message)
    {
        $catalog = $this->catalogRepository->find($message->getId());

        if (!$catalog instanceof Catalog) {
            return;
        }
        $this->logger->debug($this->workflow->can($catalog, 'handle'));
        $this->logger->debug($this->workflow->can($catalog, 'sync'));

        //Check in what state of the workflow is the Catalog
        if ($this->workflow->can($catalog, 'handle')) {
            //Process JSON file and import the products
            $handled = $this->catalogImport->handleSingle($catalog);

            //Check the result of the import process and if it's ok change the state to imported and dispatch the Message to Sync the products
            if ($handled) { 
                $this->workflow->apply($catalog, 'handle');
                $this->entityImport->flush();
                $this->bus->dispatch($message);
            }
        } elseif ($this->workflow->can($catalog, 'sync')) {
            //Sync the products to the SFTP server by creating a CSV file
            $synced = $this->catalogExport->handleSingle($catalog);

            //Check the result of the export process and if it's ok change the state to sync
            if ($synced) {
                $this->workflow->apply($catalog, 'sync');
                $this->entityImport->flush();
                $this->bus->dispatch($message);
            }
        } elseif ($this->logger) {
            $this->logger->debug('Dropping catalog message', ['catalog' => $catalog->getId(), 'state' => $catalog->getState()]);
        }
    }
}
