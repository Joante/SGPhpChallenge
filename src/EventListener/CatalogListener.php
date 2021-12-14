<?php

namespace App\EventListener;

use App\Entity\Catalog;
use App\Message\CatalogMessage;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CatalogListener implements EventSubscriberInterface
{
/**
     * @var Catalog
     */
    private $catalog;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var string
     */
    private $catalogsDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(MessageBusInterface $bus, string $catalogsDir, LoggerInterface $logger)
    {
        $this->bus = $bus;
        $this->catalogsDir = $catalogsDir;
        $this->logger = $logger;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['checkImportDirectory'],
            AfterEntityPersistedEvent::class => ['dispatchMessage'],
            AfterEntityDeletedEvent::class => ['removeFiles'],
        ];
    }

    public function checkImportDirectory(BeforeEntityPersistedEvent $event)
    {
        //Check if the directory to store JSON files exists, if not create it
        if (!file_exists($this->catalogsDir)) {
            mkdir($this->catalogsDir, 0777);
        }
    }

    public function dispatchMessage(AfterEntityPersistedEvent $event)
    {
        if (!$event->getEntityInstance() instanceof Catalog) {
            return;
        }

        $this->catalog = $event->getEntityInstance();

        //Dispatch the message to quque the process of import
        $this->bus->dispatch(new CatalogMessage($this->catalog->getId()));
    }

    public function removeFiles(AfterEntityDeletedEvent $event)
    {
        if (!$event->getEntityInstance() instanceof Catalog) {
            return;
        }

        $this->catalog = $event->getEntityInstance();

        //When eliminated remove the JSON files from the directory
        $absolutePath = $this->catalogsDir.'/'.$this->catalog->getFilePath();
        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
