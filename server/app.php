<?php

require __DIR__ . '/../vendor/autoload.php';
$autoloader = require_once __DIR__  . '/../vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
var_dump(__DIR__);
$autoloader->setPsr4(
    '',
    '../../rw-notifications/vendor'
);

use Monolog\Logger;
use RA\NotificationsBundle\Model\Configuration\Configuration;
use RA\NotificationsBundle\Model\Context\ContextManager;
use RA\NotificationsBundle\Model\Device\DeviceManager;
use RA\NotificationsBundle\Providers\Pusher;


$log = new Logger('name');
$log->pushHandler(new Monolog\Handler\StreamHandler('log/app.log', Monolog\Logger::WARNING));
$log->addWarning('Foo');

$contextManager = new ContextManager(
    [
        'server_key' => '',
        'fcm_server' => ''
    ],[
        'push_passphrase' => '',
        'push_certificate' => '',
        'apns_server' => '',
    ],[
        'class' => ''
    ],[
        'americas' => [
            'ios' => [
                'push_certificate' => 'odfmldkfsmlfdkmsd',
                'apns_topic' => 'odfmldkfsmlfdkmsd',
            ]
        ],
        'word' => [
            'ios' => [
                'push_certificate' => 'odfmldkfsmlfdkmsd',
                'apns_topic' => 'odfmldkfsmlfdkmsd',
            ]
        ]
    ]
);


$db_configuration = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'dbname' => 'reliefweb_notif',
    'user' => 'reliefweb_notif',
    'password' => '9chLMXEQ9nVCBsnf',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
);
$db_connection = $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $db_configuration);
$pusher = new Pusher($contextManager, $log);
