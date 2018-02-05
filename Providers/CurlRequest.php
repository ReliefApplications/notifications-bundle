<?php
/**
 * Created by PhpStorm.
 * User: intern2
 * Date: 25/01/18
 * Time: 09:30
 */

namespace RA\NotificationsBundle\Providers;

use Monolog\Logger;
use RA\NotificationsBundle\Model\Context\Context;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\DeviceInterface;
use RA\NotificationsBundle\Model\Pusher\IosPusher;

class CurlRequest
{
    const Android   = 1;
    const IosHttp2  = 2;
    const IosLegacy = 3;

    /**
     * @var ContextManager $contextManager
     */
    private $contextManager;

    /**
     * @var Logger $logger
     */
    private $logger;

    function __construct(ContextManager $contextManager, Logger $logger)
    {
        $this->contextManager = $contextManager;
        $this->logger = $logger;
        $this->init();
    }

    function init()
    {
        $ch = curl_init();

        if (!$ch) {
            $this->logger->error('Could not init CURL.');
            throw new PusherException("The pusher requires the curl module to be installed");
        }

        return $ch;
    }

    function destroy($ch)
    {
        curl_close($ch);
    }

    /**
     * @param string $url
     * @param array $headers
     * @param array|string $fields array for the android pusher and json string the ios pusher
     * @param \closure $success
     * @param \closure $fail
     * @return mixed
     */
    function sendAndroid(string $url, array $headers, $fields, \closure $success, \closure $fail)
    {
        $ch = $this->init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

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


    function setIosHttp2Options(&$ch, array $headers, string $fields, Context $context)
    {
        $certificate = $context->getKeys()['ios']['push_certificate'];

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->contextManager->getConfiguration()->getIosPushPassphrase());
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, IosPusher::IOS_HTTP_TIMEOUT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    }

    function sendIosHttp2($ch, string $url, DeviceInterface $device, \closure $success, \closure $fail)
    {

        $token = $device->getToken();
        $url = $url.$token;
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, $header_size);
        $this->logger->debug("response sent");
        $this->logger->debug($response);

        switch ($httpcode) {
            case 200: // 200 Success
                $this->logger->debug('APNs server returned : ' . $response);
                $return = $success($response, $httpcode, $this->logger);
                break;

            case 0:
                $message = sprintf('Unable to connect to the APNs server : %s', $response . curl_error($ch));
                $this->logger->error($message);
                if (preg_match('/HTTP\/2/', $response)) {
                    $message .= ' HTTP2 does not seem to be supported by CURL on your server. Please upgrade your setup (with nghttp2) or use the APNs\' "legacy" protocol.';
                    $this->logger->warning($message);
                }
                $return = $fail($message, $httpcode, $this->logger);
                break;

            case 410: // 410 The device token is no longer active for the topic.
                $response_array = json_decode($body, true);

                $message = 'APNs server returned  : (' . $httpcode . ') ' . $response_array["reason"];
                $this->logger->debug($message);

                $message2 = 'Device is no longer active, device removed from database.';
                $this->logger->debug($message2);

                $return = $fail($message." ".$message2, $httpcode, $this->logger);
                break;

            case 400: // 400 Bad request
                $response_array = json_decode($body, true);

                $message = 'APNs server returned  : (' . $httpcode . ') ' . $response_array["reason"];
                $this->logger->debug($message);

                $return = $fail($message, $httpcode, $this->logger);
                break;

            default:
                // 403 There was an error with the certificate or with the provider authentication token
                // 405 The request used a bad :method value. Only POST requests are supported.
                // 413 The notification payload was too large.
                // 429 The server received too many requests for the same device token.
                // 500 Internal server error
                // 503 The server is shutting down and unavailable.
                $message = 'APNs server returned  : (' . $httpcode . ') ' . $response;
                $this->logger->error($message);
                $return = $fail($message, $httpcode, $this->logger);
                break;
        }
    }

    /**
     * @param array $deviceTokens
     * @param string $payload
     * @param Context $context
     * @param \closure $success
     * @param \closure $fail
     */
    public function sendIosLegacy(array $deviceTokens, string $payload, Context $context, \closure $success, \closure $fail){
        $configuration = $this->contextManager->getConfiguration();

        $certificate = $context->getKeys()['ios']['push_certificate'];

        // Check if certificate exists
        if (file_exists($certificate))
        {
            $this->logger->info("iOS certificate detected");


            // Slicing the tokens in arrays of 10 to limit damage in case of error
            if (IosPusher::IOS_NOTIFICATION_CHAIN_LENGTH > 0) {
                $chunked_tokens = array_chunk($deviceTokens, IosPusher::IOS_NOTIFICATION_CHAIN_LENGTH);
            } else {
                $chunked_tokens = array($deviceTokens);
            }

            $errors = [];
            foreach ($chunked_tokens as $token_chain) {
                $stream_ctx = stream_context_create();
                stream_context_set_option($stream_ctx, 'ssl', 'local_cert', $certificate);
                stream_context_set_option($stream_ctx, 'ssl', 'passphrase', $configuration->getIosPushPassphrase());

                // Open a connection to the APNS server
                $fp = stream_socket_client(
                    'ssl://gateway.push.apple.com:2195',
                    $err,
                    $errstr,
                    60,
                    STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
                    $stream_ctx
                );

                if (!$fp) {
                    $text = 'Connection du APNS server failed (code ' . $err . ') : ' . $errstr;
                    $this->logger->error($text);
                    $errors[] = $text;
                    continue;
                }

                foreach ($token_chain as $id) {
                    // Build the binary notification
                    $msg = chr(0) . pack('n', 32) . pack('H*', $id) . pack('n', strlen($payload)) . $payload;

                    // Send it to the server
                    fwrite($fp, $msg, strlen($msg));
                }
                $this->logger->debug('iOS notification chain sent.');

                fclose($fp);
            }

            if(! empty($errors)){
                $fail(join(', ', $errors), 500);
            }else{
                $success("ok", 200);
            }

        }else{
            $text = "No iOS certificate detected.";
            $this->logger->error($text);
            $fail($text, 500);
        }

    }
}