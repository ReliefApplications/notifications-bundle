<?php

namespace RA\NotificationsBundle\Providers;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use RA\NotificationsBundle\Model\Context\Context;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\Device;
use RA\NotificationsBundle\Model\Device\DeviceInterface;
use RA\NotificationsBundle\Model\Device\DeviceManager;
use RA\NotificationsBundle\Model\Device\DeviceManagerInterface;
use RA\NotificationsBundle\Model\Notification\NotificationBody;
use RA\NotificationsBundle\Model\Pusher\AndroidPusher;
use RA\NotificationsBundle\Model\Pusher\IosPusher;

/**
 * Pusher
 */
class Pusher{

    /**
     * @var ContextManager $contextManager
     */
    private $contextManager;

    /**
     * function onSuccess($response, $status);
     * @var \closure $onSuccess
     */
    public $onSuccess;

    /**
     * function onError($message, $error);
     * @var \closure $onError
     */
    public $onError;

    public function __construct(ContextManager $contextManager, Logger $logger = null)
    {
        $this->contextManager   = $contextManager;
        $this->logger           = $logger;
    }

    /**
     * @return ContextManager
     */
    public function getContextManager(): ContextManager
    {
        return $this->contextManager;
    }

    public function guessContext($context)
    {
        if(empty($context)){
            $context = $this->getContextManager()->guessContext();
        }else{
            if(is_string($context)){
                $context = $this->getContextManager()->getContext($context);
            }
        }
        return $context;
    }

    /**
     * @param $data
     * @param string|Context $context The context name or an instance of Context
     * @return int
     * @throws PusherException
     */
    public function pushToMany(NotificationBody $body, array $devices, $context = null)
    {
        $context = $this->guessContext($context);

        $androidTargets = [];
        $iosTargets = [];

        $this->allow($body, $devices, $context);

        $this->sortDevices($devices, $androidTargets, $iosTargets);

        $aPusher = new AndroidPusher($this->contextManager, $androidTargets, $this->logger);
        $iPusher = new IosPusher($this->contextManager, $iosTargets, $this->logger);

        $aPusher->onSuccess = $iPusher->onSuccess = $this->onSuccess;
        $aPusher->onError = $iPusher->onError = $this->onError;

        return $aPusher->pushToMany($body, $context) + $iPusher->pushToMany($body, $context);
    }

    /**
     * @param NotificationBody $body
     * @param DeviceInterface $device  a deviceInterface instance
     * @param Context|null $context
     */
    public function pushToOne(NotificationBody $body, DeviceInterface $device, $context = null){
        $context = $this->guessContext($context);

        $target = ($device instanceof DeviceInterface) ? $device->getToken() : $device;

        $this->allow($body, $target, $context);

        $aPusher = new AndroidPusher($this->contextManager, $target, $this->logger);
        $iPusher = new IosPusher($this->contextManager, $device, $this->logger);

        $aPusher->onSuccess = $iPusher->onSuccess = $this->onSuccess;
        $aPusher->onError = $iPusher->onError = $this->onError;

        $count = 0;
        if($device->isAndroid()){
            $count = $aPusher->pushToOne($body, $context);
        }else{
            $count = $iPusher->pushToOne($body, $context);
        }
        return  $count;
    }

    public function pushToGroup(NotificationBody $body, string $targetGroup, $context = null){
        $context = $this->guessContext($context);

        $this->allow($body, $targetGroup, $context);

        $aPusher = new AndroidPusher($this->contextManager, $targetGroup, $this->logger);
        $iPusher = new IosPusher($this->contextManager, $targetGroup, $this->logger);

        $aPusher->onSuccess = $iPusher->onSuccess = $this->onSuccess;
        $aPusher->onError = $iPusher->onError = $this->onError;

        return $aPusher->pushToGroup($body, $context) + $iPusher->pushToGroup($body, $context);
    }

    /**
     * @param $data
     * @param string|array $targets
     * @param Context $context The set or guessed context
     * @throws PusherException if the target list or the content or the context are empty
     */
    public function allow($data, $targets, Context $context = null)
    {
        if(empty($context)){
            throw new PusherException("The pusher requires a context");
        }

        if(empty($targets)){
            throw new PusherException("The pusher requires a list of targets");
        }

        if(empty($data)){
            throw new PusherException("The pusher requires a content");
        }
    }

    /**
     * Sort devices following their type and ignore devices for which notifications are not enabled.
     * Devices without token are ignored.
     *
     * @param string|array $devices
     * @param array $androidDevices
     * @param array $iosDevices
     * @return int the number of devices targetted
     */
    public function sortDevices($devices, array &$androidDevices, array &$iosDevices)
    {
        if( ! is_array($devices)){
            return 1;
        }

        $androidDevices = [];
        $iosDevices = [];

        foreach ($devices as $device)
        {
            if($device instanceof DeviceInterface)
            {
                if( ! $device->isPushEnabled()){
                    continue;
                }

                if(empty($device->getToken())){
                    $this->logger->warning("Tokenless device ignored. UUID : ".$device->getUUID());
                    continue;
                }

                if($device->isAndroid())
                {
                    array_push($androidDevices, $device);
                    $this->logger->debug("Android device detected. Key : ".$device->getToken());

                }else if($device->isIos()) {

                    array_push($iosDevices, $device);
                    $this->logger->debug("iOS device detected. Key : ".$device->getToken());

                }else{
                    $this->logger->warning(sprintf("The type of the device [uuid=%s] is not supported. Supported types : Android=>0, Ios=>1"));
                }
            }
        }
    }


}