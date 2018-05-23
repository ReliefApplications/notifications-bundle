<?php
/**
 * Created by PhpStorm.
 * User: intern2
 * Date: 23/01/18
 * Time: 20:54
 */

namespace RA\NotificationsBundle\Model\Device;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Device
 * @package RA\NotificationsBundle\Model\Device
 * @ORM\MappedSuperclass
 */
abstract class Device implements DeviceInterface
{
    use DeviceTrait;

    const ANDROID   = 0;
    const IOS       = 1;
    const WINDOWS   = 2;

    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="uuid", type="string", length=255)
    //  */
    // protected $uuid = '';

    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="token", type="string", length=255, nullable=true)
    //  */
    // protected $token = '';

    // /**
    //  * @var int
    //  *
    //  * @ORM\Column(name="type", type="smallint", nullable=true)
    //  */
    // protected $type;

    // /**
    //  * @var boolean
    //  *
    //  * @ORM\Column(name="pushEnabled", type="boolean", options={"default" : 1})
    //  */
    // protected $pushEnabled = true;

    public function __construct()
    {
        $this->setToken('')
            ->setUuid('')
            ->setPushEnabled(true)
        ;
    }

    function isAndroid() : bool {
        return $this->type == self::ANDROID;
    }

    function isIos() : bool {
        return $this->type == self::IOS;
    }

    function isWindows() : bool {
        return $this->type == self::WINDOWS;
    }
}