<?php

namespace RA\NotificationsBundle\Providers;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use RA\NotificationsBundle\Model\Context\ContextManager;

/**
 * Pusher
 */
class Pusher{

    /**
     * @var ContextManager $contextManager
     */
    private $contextManager;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    public function __construct(ContextManager $contextManager, EntityManager $entityManager, Logger $logger = null)
    {
        $this->contextManager   = $contextManager;
        $this->entityManager    = $entityManager;
        $this->logger           = $logger;
    }

    /**
     * @return ContextManager
     */
    public function getContextManager(): ContextManager
    {
        return $this->contextManager;
    }


}