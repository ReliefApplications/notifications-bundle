<?php

namespace RA\NotificationsBundle\Model\Device;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeviceManager implements DeviceManagerInterface
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @var string $deviceClassManaged
     */
    private $deviceClassManaged;

    public function __construct(EntityManagerInterface $entityManager, string $deviceClassManaged)
    {
        $this->entityManager = $entityManager;
        $this->deviceClassManaged = $deviceClassManaged;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository($this->getDeviceClassManaged());
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return DeviceManagerInterface
     */
    public function setEntityManager(EntityManagerInterface $entityManager) : DeviceManagerInterface
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    public function getDeviceClassManaged() : string
    {
        return $this->deviceClassManaged;
    }

    public function setDeviceClassManaged(string $deviceClassManaged) : DeviceManagerInterface
    {
        $this->deviceClassManaged = $deviceClassManaged;
        return $this;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param string $class
     * @return DeviceManagerInterface
     */
    public static function newInstance(EntityManagerInterface $entityManager, string $deviceClassManaged, string $managerClass = DeviceManager::class): DeviceManagerInterface
    {
        return new $managerClass($entityManager, $deviceClassManaged);
    }

    /**
     * @param $uuid
     * @param $platform
     * @return mixed|DeviceInterface
     */
    public function create($uuid, $platform): DeviceInterface
    {
        $class = $this->getDeviceClassManaged();
        return new $class($uuid, $platform);
    }

    /**
     * @param DeviceInterface $device
     * @param bool $flush
     * @return mixed
     */
    public function save(DeviceInterface $device, bool $flush = true): DeviceInterface
    {
        $this->entityManager->persist($device);
        if ($flush) {
            $this->entityManager->flush();
        }
        return $device;
    }

    /**
     * @param DeviceInterface $device
     * @return mixed
     */
    public function remove(DeviceInterface $device, bool $flush = true)
    {
        $this->entityManager->remove($device);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param DeviceInterface $device
     * @return mixed|DeviceInterface
     */
    public function refresh(DeviceInterface $device): DeviceInterface
    {
        $this->entityManager->refresh($device);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param array $types
     * @return array
     */
    public function findByPlatform(array $types): array
    {
        return $this->getRepository()->findBy(['type' => $types]);
    }

    /**
     * @param string $uuid
     * @return mixed
     */
    public function findByUUID(string $uuid)
    {
        return $this->getRepository()->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @param string $token
     * @return mixed
     */
    public function findByToken(string $token)
    {
        return $this->getRepository()->findOneBy(['token' => $token]);
    }
}