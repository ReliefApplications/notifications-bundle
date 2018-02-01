<?php

require __DIR__ . '/../vendor/autoload.php';
$autoloader = require_once __DIR__  . '/../vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


use Monolog\Logger;
use RA\NotificationsBundle\Model\Configuration\Configuration;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\Device;
use RA\NotificationsBundle\Model\Device\DeviceManager;
use RA\NotificationsBundle\Model\Notification\NotificationBody;
use RA\NotificationsBundle\Providers\Pusher;
use RA\NotificationsBundle\Providers\PusherException;

// Declare a custom class
class CustomDevice extends Device{

}

// Declare a logger. it's required by the pusher to log error
$logger = new Logger('name');
$logger->pushHandler(new Monolog\Handler\StreamHandler('log/app.log', Monolog\Logger::WARNING));
$logger->addWarning('Foo');

// Declare the context with required parameters (see gist : https://gist.github.com/blixit/c6660d2a8a57e0d519e72c1ee3c89293)
$contextManager = new ContextManager(
    'AIzaSyB0a4l5R9PgEhZwuzknK7UE7qRGxYiuqP4', 
    'fcm.googleapis.com',
    '__',
    '__',
    'api.development.push.apple.com' ,
    '__' ,
    '__' ,
    CustomDevice::class,
    DeviceManager::class,
     [
         'ctx_americas' => [
             'ios' => [
                 'push_certificate' => '/var/ioskeys/americas.pem',
                 'apns_topic' => 'org.reliefweb.americas',
             ]
         ]

    ]
);


// Declare the pusher
$pusher = new Pusher($contextManager, $logger);

// Declare callbacks to handle requests on success and error
$pusher->onSuccess = function ($response){
    var_dump($response);
};
$pusher->onError = function ($error, $message){
    var_dump(sprintf("Error : %s => %s", $error, $message));
};

// Declare the notification
$body           = new NotificationBody();
$body->setTitle("New features here ! Come take a look !!");
$body->setBody("We just developed a new feature that should interest you");

/**
 * Declare a token (took from the frontend app)
 * For demonstration purpose, we hardcoded the token, on production, please fetch your array of tokens from the Firebase database.
 */
$token = 'd_GI27yWSYM:APA91bGDjnrA9mu3nYxeNfaKhASxjvkk_C9oQgDOp9evJtXXcoNoxS0JTXi_qRWxk1-_WYGFGyv_-zmXMpoKoQpyzikykNPDJyByjamXOo0dobmKJmjc3-Hc7THViTEgIkWLABXJ5rIL';
// Declare a device with this token
$singleDevice = (new CustomDevice())
    ->setToken($token)
    ->setPushEnabled(1); // default value is 'true'

try{
    //simulate an array i=of devices
    $targetsMany = [ $singleDevice ];
    //push to many devices
    $count = $pusher->pushToMany($body, $targetsMany, "ctx_americas");

    //push to a single device
    $count = $pusher->pushToOne($body, $singleDevice, "ctx_americas");
    //$count = $pusher->pushToOne($body, $singleDevice, "ctx_americas");
    //$count = $pusher->pushToOne($body, $targetsMany[0], "ctx_americas");

    //push to a group or a topic
    $targetsGroup = '/topics/anytopic';
    $count = $pusher->pushToGroup($body, $targetsGroup, "ctx_americas");

}catch (PusherException $e){
    var_dump($e->getMessage());
}

