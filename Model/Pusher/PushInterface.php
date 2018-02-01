<?php

namespace RA\NotificationsBundle\Model\Pusher;

use Monolog\Logger;
use RA\NotificationsBundle\Model\Context\Context;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\DeviceManagerInterface;
use RA\NotificationsBundle\Model\Notification\NotificationBody;

interface PushInterface
{

    public function __construct(ContextManager $contextManager, $targets, Logger $logger = null);

    /**
     * Return a formatted string that contains the payload
     * @return string|array
     */
    function getDataPayload(NotificationBody $body);

    function getNotificationPayload(NotificationBody $body);

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToOne(NotificationBody $body, $context) : int;

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToMany(NotificationBody $body, $context) : int;

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToGroup(NotificationBody $body, $context) : int;

//    function send();

}