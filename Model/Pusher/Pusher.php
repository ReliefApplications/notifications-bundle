<?php
/**
 * Created by PhpStorm.
 * User: intern2
 * Date: 25/01/18
 * Time: 19:11
 */

namespace RA\NotificationsBundle\Model\Pusher;


use Monolog\Logger;
use RA\NotificationsBundle\Model\Configuration\Configuration;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\DeviceInterface;
use RA\NotificationsBundle\Model\Device\DeviceManagerInterface;

class Pusher
{
    const ANDROID_FCM_SERVER_URL = "https://%s/fcm/send";
    /**
     * @var ContextManager $contextManager
     */
    protected $contextManager;

    /**
     * @var Configuration $configuration
     */
    protected $configuration;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var string|DeviceInterface|array $targets
     */
    protected $targets;

    /**
     * @var string $url
     */
    private $url = "";
    /**
     * @var array $headers
     */
    private $headers = [];

    /**
     * @var bool $targetsFieldIsString
     */
    protected $targetsFieldIsString;

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


    public function __construct(ContextManager $contextManager, $targets, Logger $logger = null){
        $this->setTargets($targets);

        $this->contextManager = $contextManager;
        $this->configuration = $this->contextManager->getConfiguration();
        $this->logger = $logger;
        $this->url    = sprintf(self::ANDROID_FCM_SERVER_URL, $this->configuration->getAndroidFcmServer());
        $this->headers = array(
            sprintf('Authorization: key= %s', $this->configuration->getAndroidServerKey()),
            'Content-Type: application/json'
        );
    }

    /**
     * @return array|DeviceInterface|string
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @param array|DeviceInterface|string $targets
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;
        $this->targetsFieldIsString = is_array($targets) ? false : true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTargetsFieldIsString(): bool
    {
        return $this->targetsFieldIsString;
    }

    /**
     * @return string
     */
    public function getUrl() : string {
        return $this->url;
    }


    public function extractTokens($devices) : array
    {
        return is_array($devices) ? array_map(function (DeviceInterface $obj) {
            return $obj->getToken();
        }, $devices) : [];
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }

}