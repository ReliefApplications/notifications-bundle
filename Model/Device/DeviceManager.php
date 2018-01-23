<?php

namespace RA\NotificationsBundle\Model\Device;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeviceManager implements DeviceManagerInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        //
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        // TODO: Implement getRepository() method.
    }

    /**
     * @param $uuid
     * @param $platform
     * @return mixed|DeviceInterface
     */
    public function create($uuid, $platform): DeviceInterface
    {
        // TODO: Implement create() method.
    }

    /**
     * @param DeviceInterface $device
     * @return mixed
     */
    public function remove(DeviceInterface $device)
    {
        // TODO: Implement remove() method.
    }

    /**
     * @param DeviceInterface $device
     * @return mixed|DeviceInterface
     */
    public function refresh(DeviceInterface $device): DeviceInterface
    {
        // TODO: Implement refresh() method.
    }

    /**
     * @param DeviceInterface $device
     * @param bool $flush
     * @return mixed
     */
    public function save(DeviceInterface $device, bool $flush = true): DeviceInterface
    {
        // TODO: Implement save() method.
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @param array $types
     * @return array
     */
    public function findByPlatform(array $types): array
    {
        // TODO: Implement findByPlatform() method.
    }

    /**
     * @param string $uuid
     * @return DeviceInterface
     */
    public function findByUUID(string $uuid): DeviceInterface
    {
        // TODO: Implement findByUUID() method.
    }
}