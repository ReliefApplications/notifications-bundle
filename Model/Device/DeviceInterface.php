<?php
/**
 * Created by PhpStorm.
 * User: intern2
 * Date: 23/01/18
 * Time: 20:57
 */

namespace RA\NotificationsBundle\Model\Device;


interface DeviceInterface
{
    public function getUuid();

    public function setUuid($uuid);

    public function getToken();

    public function setToken($token);

    public function getType();

    public function setType($type);

    function isAndroid() : bool;

    function isIos() : bool;

    function isWindows() : bool;


}