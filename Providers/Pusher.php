<?php

namespace RA\NotificationsBundle\Providers;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use RA\NotificationsBundle\Model\Context\Context;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\DeviceInterface;
use RA\NotificationsBundle\Model\Device\DeviceManager;
use RA\NotificationsBundle\Model\Device\DeviceManagerInterface;
use RA\NotificationsBundle\Model\Notification\NotificationBody;

/**
 * Pusher
 */
class Pusher{

    /**
     * @var ContextManager $contextManager
     */
    private $contextManager;

    /**
     * @var DeviceManagerInterface $deviceManager
     */
    private $deviceManager;

    public function __construct(ContextManager $contextManager, DeviceManagerInterface $deviceManager, Logger $logger = null)
    {
        $this->contextManager   = $contextManager;
        $this->deviceManager    = $deviceManager;
        $this->logger           = $logger;
    }

    /**
     * @return ContextManager
     */
    public function getContextManager(): ContextManager
    {
        return $this->contextManager;
    }

    /**
     * @return ContextManager
     */
    public function getDeviceManager(): DeviceManagerInterface
    {
        return $this->deviceManager;
    }

    /**
     * @param $data
     * @param array $targets
     * @param string|Context $context The context name or an instance of Context
     * @return int
     * @throws PusherException
     */
    public function push(NotificationBody $body, array $targets, $context = null)
    {
        if(empty($context)){
            /** @var Context $context */
            $context = $this->getContextManager()->guessContext();
        }else{
            if(is_string($context)){
                /** @var Context $context */
                $context = $this->getContextManager()->getContext($context);
            }
        }

        $this->allow($body, $targets, $context);

        $this->sortDevices($targets, $androidDevices, $iosDevices);

        $count = $this->pushAndroid($body, $androidDevices, $context);

        $count += $this->pushIos($body, $iosDevices, $context);

        return $count;
    }

    /**
     * @param $data
     * @param array $targets
     * @param Context $context The set or guessed context
     * @throws PusherException if the target list or the content or the context are empty
     */
    public function allow($data, array $targets, Context $context = null)
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
     * Sort devices following their type.
     * Devices without token are ignored.
     *
     * @param array $devices
     * @param array $androidDevices
     * @param array $iosDevices
     * @return int the number of devices targetted
     */
    public function sortDevices(array $devices, array &$androidDevices, array &$iosDevices)
    {
        $androidDevices = [];
        $iosDevices = [];

        foreach ($devices as $device)
        {
            if($device instanceof DeviceInterface)
            {
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

    public function pushAndroid(NotificationBody $body, array $targets, Context $context) : int
    {
        if(empty($targets)){
            return 0;
        }

        $configuration = $this->getContextManager()->getConfiguration();

        $url    = sprintf("https://%s/fcm/send", $configuration->getAndroidFcmServer());
        $apiKey = $configuration->getAndroidServerKey();

        $tokens = $this->extractTokens($targets);
        $fields = array(
            'to'  => $tokens,
            'data' => $body->getPayload(NotificationBody::PAYLOAD_ARRAY_ANDROID),
        );
        $this->logger->debug("Android Payload : " . json_encode($fields));

        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        );

        $curl = new CurlRequest($this->logger);
        $deviceManager = $this->getDeviceManager();

        $curl->send(CurlRequest::Android, $url, $headers, $fields, function ($response, $httpcode, Logger $logger) use($targets, $tokens, $deviceManager)
        {
            $response_array = json_decode($response, true);
            foreach ($response_array['results'] as $key => $result)
            {
                // The user removed the app
                if (array_key_exists('error', $result) && $result['error'] == 'NotRegistered')
                {
                    $deviceManager->remove($targets[$key]);
                    $logger->debug('Device ' . $tokens[$key] . ' is no longer active, device removed from database.');
                }

                // The user removed the app
                if (array_key_exists('error', $result) && $result['error'] == 'InvalidRegistration')
                {
                    $targets[$key]->setToken(null);
                    $deviceManager->save($targets[$key]);
                    $logger->warning('Bad device Token for ' . $tokens[$key] . ', token removed from database.');
                }
            }
        }, function($error, $httpcode){
            //the error is already logged. Here do what you want with the $error and and the http code
        });

        return count($tokens);
    }

    public function pushIos(NotificationBody $body, array $targets, Context $context) : int
    {
        if(empty($targets)){
            return 0;
        }


        return count($targets);
    }

    public function extractTokens(array $devices) : array
    {
        return array_map(function (DeviceInterface $obj) {
            return $obj->getToken();
        }, $devices);
    }


}