<?php

namespace RA\NotificationsBundle\Model\Pusher;

use Monolog\Logger;
use RA\NotificationsBundle\Model\Context\Context;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\DeviceManagerInterface;
use RA\NotificationsBundle\Model\Notification\NotificationBody;
use RA\NotificationsBundle\Providers\CurlRequest;
use RA\NotificationsBundle\Providers\PusherException;

class IosPusher extends Pusher implements PushInterface
{
    // APNs Legacy iOS notifications are send by series of this length. Set to -1 to disable.
    // Warning: When set to 1, the system is not scalable. To many notifications will be consider a DDoS attack by APNs servers
    const IOS_NOTIFICATION_CHAIN_LENGTH = 1;

    const IOS_HTTP_TIMEOUT = 1000;

    public function __construct(ContextManager $contextManager, $targets, Logger $logger = null){
        parent::__construct($contextManager, $targets, $logger);
        $this->headers = [
            sprintf("apns-topic: %s", $contextManager->getConfiguration()->getIosApnsTopic())
        ];
        $this->url    = sprintf(self::IOS_FCM_SERVER_URL, $this->contextManager->getConfiguration()->getIosApnsServer());
    }

    /**
     * Return a formatted string that contains the data payload
     * @return string
     */
    function getDataPayload(NotificationBody $body): string
    {
        return json_encode([]);
    }

    /**
     * Return a formatted string that contains the notification payload
     * @return string
     */
    function getNotificationPayload(NotificationBody $body)
    {
        $payload = array(
            "aps" => array(
                "alert" => array(
                    "title" => $body->getTitle(),
                    "body"  => $body->getBody(),
                ),
            )
        );
        $methods = get_class_methods($body);

        if(array_key_exists("getBadge", $methods) && $body->getBadge()){
            $payload["aps"]["badge"] = $body->getBadge();
        }
        if(array_key_exists("getSound", $methods) && $body->getSound()){
            $payload["aps"]["sound"] = $body->getSound();
        }
        if(array_key_exists("getCategory", $methods) && $body->getCategory()){
            $payload["aps"]["category"] = $body->getCategory();
        }
        if(array_key_exists("getClickAction", $methods) && $body->getClickAction()){
            $payload["aps"]["click_action"] = $body->getClickAction();
        }
        if(array_key_exists("getSubtitle", $methods) && $body->getSubtitle()){
            $payload["aps"]["subtitle"] = $body->getSubtitle();
        }
        if(array_key_exists("getAdditionalFields", $methods) && $body->getAdditionalFields()){
            $additionalFields = $body->getAdditionalFields();
            foreach($additionalFields as $additionalField){
                if(array_key_exists("key", $additionalField) && array_key_exists("value", $additionalField)){
                    $payload["aps"][$additionalField["key"]] = $additionalField["value"];
                }
            }
        }

        return json_encode($payload);
    }

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToOne(NotificationBody $body, $context): int
    {
        $configuration = $this->contextManager->getConfiguration();
        $deviceTokens = [$this->getTargets()->getToken()];

        $curl   = new CurlRequest($this->contextManager, $this->logger);
        $ch     = $curl->init();

        // Encode the payload as JSON
        $payload = $this->getNotificationPayload($body);
        $this->logger->debug("iOS Payload : $payload");

        if($configuration->getIosProtocol() == "http2"){

            if( ! $this->checkHttp2()){
                throw new PusherException(self::HTTP2_ERROR_MESSAGE);
            }

            $curl->setIosHttp2Options($ch, $this->getHeaders(), $payload, $context);
            $curl->sendIosHttp2($ch, $this->getUrl(), $this->getTargets(), $this->onSuccess, $this->onError );

        }else{
            $curl->sendIosLegacy($deviceTokens, $payload, $context, $this->onSuccess, $this->onError );
        }

        $curl->destroy($ch);

        return 0;
    }

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToMany(NotificationBody $body, $context): int
    {
        if( $this->isTargetsFieldIsString()){
            throw new PusherException("The method pushToMany() expects an array of devices as target");
        }

        $configuration = $this->contextManager->getConfiguration();
        $deviceTokens = $this->extractTokens($this->getTargets());

        $curl   = new CurlRequest($this->contextManager, $this->logger);
        $ch     = $curl->init();

        // Encode the payload as JSON
        $payload = $this->getNotificationPayload($body);
        $this->logger->debug("iOS Payload : $payload");

        if($configuration->getIosProtocol() == "http2"){

            if( ! $this->checkHttp2()){
                throw new PusherException(self::HTTP2_ERROR_MESSAGE);
            }

            $curl->setIosHttp2Options($ch, $this->getHeaders(), $payload, $context);
            foreach ($this->getTargets() as $device) {
                $curl->sendIosHttp2($ch, $this->getUrl(), $device, $this->onSuccess, $this->onError );
            }

        }else{
            $curl->sendIosLegacy($deviceTokens, $payload, $context, $this->onSuccess, $this->onError );
        }

        $curl->destroy($ch);
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
