<?php
/**
 * Created by PhpStorm.
 * User: intern2
 * Date: 25/01/18
 * Time: 09:30
 */

namespace RA\NotificationsBundle\Providers;

use Monolog\Logger;

class CurlRequest
{
    const Android   = 1;
    const IosHttp2  = 2;
    const IosLegacy = 3;

    /**
     * @var Logger $logger
     */
    private $logger;

    function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->init();
    }

    private function init(){
        $ch = curl_init();

        if (!$ch) {
            $this->logger->error('Could not init CURL.');
            throw new PusherException("The pusher requires the curl module to be installed");
        }

        return $ch;
    }

    function send(int $platform, string $url, array $headers, array $fields, \closure $success, \closure $fail){
        $ch = $this->init();

        switch ($platform){
            case self::Android:
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                break;
            case self::IosHttp2: $this->setIosHttp2Options($ch, $url, $headers, $fields);
                break;
            case self::IosLegacy: $this->setIosLegacyOptions($ch, $url, $headers, $fields);
                break;
        }

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);

        switch ($httpcode) {
            case 200:
                $this->logger->debug(sprintf('FCM server returned : ', json_encode($response)));
                $return = $success($response, $httpcode, $this->logger);
                break;
            case 0:
                $text = sprintf('Unable to connect to the FCM server : %s', $error);
                $this->logger->error($text);
                $return = $fail($text, $httpcode, $this->logger);
                break;
            default:
                $this->logger->error(sprintf('FCM server returned an error : (%d) %s ', $httpcode, $response));
                $return = $fail($response, $httpcode, $this->logger);
                break;
        }

        curl_close($ch);
        return $return;
    }

    private function setIosHttp2Options($curlHandler, string $url, array $headers, array $fields){


    }

    private function setIosLegacyOptions($curlHandler, string $url, array $headers, array $fields){

    }
}