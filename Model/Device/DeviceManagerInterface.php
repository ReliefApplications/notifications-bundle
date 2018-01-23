<?php
/**
 * Created by PhpStorm.
 * User: intern2
 * Date: 23/01/18
 * Time: 20:57
 */

namespace RA\NotificationsBundle\Model\Device;


use Doctrine\Common\Persistence\ObjectRepository;

interface DeviceManagerInterface
{
    /**
     * @return ObjectRepository
     */
    public function getRepository() : ObjectRepository;

    /**
     * @param $uuid
     * @param $platform
     * @return mixed|DeviceInterface
     */
    public function create($uuid, $platform) : DeviceInterface;

    /**
     * @param DeviceInterface $device
     * @return mixed
     */
    public function remove(DeviceInterface $device);

    /**
     * @param DeviceInterface $device
     * @return mixed|DeviceInterface
     */
    public function refresh(DeviceInterface $device) : DeviceInterface;

    /**
     * @param DeviceInterface $device
     * @param bool $flush
     * @return mixed
     */
    public function save(DeviceInterface $device, bool $flush = true) : DeviceInterface;

    /**
     * @return array
     */
    public function getAll() : array;

    /**
     * @param array $types
     * @return array
     */
    public function findByPlatform(array $types) : array;

    /**
     * @param string $uuid
     * @return DeviceInterface
     */
    public function findByUUID(string $uuid) : DeviceInterface;

}