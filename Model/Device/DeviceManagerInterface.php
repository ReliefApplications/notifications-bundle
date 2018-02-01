<?php
/**
 * Created by PhpStorm.
 * User: intern2
 * Date: 23/01/18
 * Time: 20:57
 */

namespace RA\NotificationsBundle\Model\Device;


use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

interface DeviceManagerInterface
{
    /**
     * DeviceManagerInterface constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $deviceClassManaged
     */
    public function __construct(EntityManagerInterface $entityManager, string $deviceClassManaged);

    /**
     * @return ObjectRepository
     */
    public function getRepository() : ObjectRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @return DeviceManagerInterface
     */
    public function setEntityManager(EntityManagerInterface $entityManager) : DeviceManagerInterface;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string $class
     * @return DeviceManagerInterface
     */
    public static function newInstance(EntityManagerInterface $entityManager, string $deviceClassManaged, string $managerClass = DeviceManager::class): DeviceManagerInterface;

    /**
     * @param $uuid
     * @param $platform
     * @return mixed|DeviceInterface
     */
    public function create($uuid, $platform) : DeviceInterface;

    /**
     * @param DeviceInterface $device
     * @param bool $flush
     * @return mixed
     */
    public function save(DeviceInterface $device, bool $flush = true) : DeviceInterface;

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
     * @return mixed
     */
    public function findByUUID(string $uuid) : ?DeviceInterface;

    /**
     * @param string $token
     * @return mixed
     */
    public function findByToken(string $uuid) : ?DeviceInterface;

}