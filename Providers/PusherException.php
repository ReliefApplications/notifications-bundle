<?php
/**
 * Created by PhpStorm.
 * User: intern2
 * Date: 24/01/18
 * Time: 08:56
 */

namespace RA\NotificationsBundle\Providers;


use Throwable;

class PusherException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("[PusherException] %s", $message), $code, $previous);
    }

}