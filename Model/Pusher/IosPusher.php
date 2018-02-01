<?php

namespace RA\NotificationsBundle\Model\Pusher;

use Monolog\Logger;
use RA\NotificationsBundle\Model\Context\Context;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\DeviceManagerInterface;
use RA\NotificationsBundle\Model\Notification\NotificationBody;

class IosPusher extends Pusher implements PushInterface
{
    public function __construct(ContextManager $contextManager, $targets, Logger $logger = null){
        parent::__construct($contextManager, $targets, $logger);
    }

    public function pushIos(NotificationBody $body, $targets, Context $context) : int
    {
        if(empty($targets)){
            return 0;
        }


        return count($targets);
    }

    /**
     * Return a formatted string that contains the payload
     * @return string
     */
    function getDataPayload(NotificationBody $body): string
    {
        // TODO: Implement getPayload() method.
    }

    function getNotificationPayload(NotificationBody $body)
    {
        // TODO: Implement getNotificationPayload() method.
    }

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToOne(NotificationBody $body, $context): int
    {
        return 0;
    }

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToMany(NotificationBody $body, $context): int
    {
        return 0;
    }

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToGroup(NotificationBody $body, $context): int
    {
        return 0;
    }
}