<?php

namespace RA\NotificationsBundle\Model\Pusher;

use Monolog\Logger;
use RA\NotificationsBundle\Model\Context\Context;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\DeviceManagerInterface;
use RA\NotificationsBundle\Model\Notification\NotificationBody;
use RA\NotificationsBundle\Providers\CurlRequest;
use RA\NotificationsBundle\Providers\PusherException;

class AndroidPusher extends Pusher implements PushInterface
{

    /**
     * AndroidPusher constructor.
     * @param $targets
     */
    public function __construct(ContextManager $contextManager, $targets, Logger $logger = null)
    {
        parent::__construct($contextManager, $targets, $logger);
    }


    /**
     * Return a formatted string that contains the payload
     * @return string
     */
    function getDataPayload(NotificationBody $body): array
    {
        $payload = array();
        if($body->getTitle()){
            $payload["title"] = $body->getTitle();
        }
        if($body->getBody()){
            $payload["message"] = $body->getBody();
        }
        if($body->getUniqId()){
            $payload["notId"] = $body->getUniqId();
        }
        if($body->getLedColor()){
            $payload["ledColor"] = $body->getLedColor();
        }
        if($body->getImage()){
            $payload["image"] = $body->getImage();
        }
        if($body->getImageType()){
            $payload["image-type"] = $body->getImageType();
        }
        if($body->getActions()){
            $payload["actions"] = $body->getActions();
        }
        if($body->getAdditionalFields()){
            $additionalFields = $body->getAdditionalFields();
            foreach($additionalFields as $additionalField){
                if(array_key_exists("key", $additionalField) && array_key_exists("value", $additionalField)){
                    $payload[$additionalField["key"]] = $additionalField["value"];
                }
            }
        }

        return $payload;
    }

    function getNotificationPayload(NotificationBody $body)
    {
        return [
            'title' => $body->getTitle(),
            'body' => $body->getBody(),
//            'android_channel_id' => $body->getAndroidChannelId(),
//            'icon' => $body->getIcon(),
//            'sound' => $body->getSound(),
//            'tag' => $body->getTag(),
//            'color' => $body->getColor(),
//            'click_action' => $body->getClickAction(),
            'badge' => $body->getBadge(),
        ];
    }

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToOne(NotificationBody $body, $context): int
    {
        if( ! $this->isTargetsFieldIsString()){
            throw new PusherException("The method PushToOne() expects a token string as target");
        }

        $token = $this->getTargets();
        $fields = array(
            'to'  => $token,
            'data' => $this->getDataPayload($body),
            'notification' => $this->getNotificationPayload($body),
        );
        $this->logger->debug("Android Payload : " . json_encode($fields));

        $curl = new CurlRequest($this->logger);
        $success = $this->onSuccess;
        $error = $this->onError;

        return $curl->send(CurlRequest::Android, $this->getUrl(), $this->getHeaders(), $fields,
            function ($response, $httpcode, Logger $logger) use($success, $error)
            {
                $response = json_decode($response, true);

                if( ! is_array($response))
                {
                    $text = sprintf("This response type is not supported yet. The FCM response is a string.");
                    $error($text, "Pusher::NotSupported");
                    $logger->error($text);
                    return 0;
                }

                if( ! array_key_exists('results', $response))
                {
                    $text = sprintf("FCM failed with this error : %s", $response['error']);
                    $error($text, $response['error']);
                    $logger->error($text);
                    return 0;
                }

                $results = $response['results'];
                foreach ($results as $result)
                {
                    // The user removed the app
                    if (array_key_exists('error', $result))
                    {
                        $text = sprintf("FCM failed with this error : %s", $result['error']);
                        $logger->error($text);
                        $error($text, $result['error']);
                        return 0;
                    }

                    if(array_key_exists('message_id', $result))
                    {
                        $text = sprintf("Notification sent with id: %s", $result['message_id']);
                        $logger->debug($text);
                        $success($text, $httpcode);
                        return 1;
                    }
                }

                return 0;

            }, function($error, $httpcode){
                //the error is already logged. Here do what you want with the $error and and the http code
                throw new PusherException($error, $httpcode);
            });
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

        $tokens = $this->extractTokens($this->getTargets());

        $fields = array(
            'registration_ids'  => $tokens,
            'data' => $this->getDataPayload($body),
            'notification' => $this->getNotificationPayload($body),
        );
        $this->logger->debug("Android Payload : " . json_encode($fields));

        $curl = new CurlRequest($this->logger);
        $success = $this->onSuccess;
        $error = $this->onError;

        return $curl->send(CurlRequest::Android, $this->getUrl(), $this->getHeaders(), $fields,
            function ($response, $httpcode, Logger $logger) use($success, $error)
            {
                $response = json_decode($response, true);

                if( ! is_array($response))
                {
                    $text = sprintf("This response type is not supported yet. The FCM response is a string.");
                    $error($text, "Pusher::NotSupported");
                    $logger->error($text);
                    return 0;
                }

                if( ! array_key_exists('results', $response))
                {
                    $text = sprintf("FCM failed with this error : %s", $response['error']);
                    $error($text, $response['error']);
                    $logger->error($text);
                    return 0;
                }

                $results = $response['results'];
                $messages = [];

                foreach ($results as $result)
                {
                    // The user removed the app
                    if (array_key_exists('error', $result))
                    {
                        $text = sprintf("FCM failed with this error : %s", $result['error']);
                        $logger->error($text);
                        $error($text, $result['error']);
                        return 0;
                    }

                    if(array_key_exists('message_id', $result))
                    {
                        $text = sprintf("Notification sent with id: %s", $result['message_id']);
                        $logger->debug($text);
                        $messages[] = $text;
                    }
                }

                if(count($messages)){
                    $success(join(',', $messages), $httpcode);
                    return 1;
                }

                return 0;

            }, function($error, $httpcode){
                //the error is already logged. Here do what you want with the $error and and the http code
                throw new PusherException($error, $httpcode);
            });
    }

    /**
     * @param NotificationBody $body
     * @param Context $context
     * @return mixed
     */
    function pushToGroup(NotificationBody $body, $context): int
    {
        if( ! $this->isTargetsFieldIsString()){
            throw new PusherException("The method pushToGroup() expects a token string as target");
        }

        $token = $this->getTargets();
        $fields = array(
            'to'  => $token,
            'data' => $this->getDataPayload($body),
            'notification' => $this->getNotificationPayload($body),
        );
        $this->logger->debug("Android Payload : " . json_encode($fields));

        $curl = new CurlRequest($this->logger);
        $success = $this->onSuccess;
        $error = $this->onError;

        return $curl->send(CurlRequest::Android, $this->getUrl(), $this->getHeaders(), $fields,
            function ($response, $httpcode, Logger $logger) use($success, $error)
            {
                $response = json_decode($response, true);

                if( ! is_array($response))
                {
                    $text = sprintf("This response type is not supported yet. The FCM response is a string.");
                    $error($text, "Pusher::NotSupported");
                    $logger->error($text);
                    return 0;
                }

                if( array_key_exists('message_id', $response))
                {
                    $text = sprintf("Notification sent with id: %s", $response['message_id']);
                    $logger->debug($text);
                    $success($text, $httpcode);
                    return 1;
                }

                if( array_key_exists('error', $response))
                {
                    $text = sprintf("FCM failed with this error : %s", $response['error']);
                    $error($text, $response['error']);
                    $logger->error($text);
                    return 0;
                }

                return 0;

            }, function($error, $httpcode){
                //the error is already logged. Here do what you want with the $error and and the http code
                throw new PusherException($error, $httpcode);
            });
    }

}