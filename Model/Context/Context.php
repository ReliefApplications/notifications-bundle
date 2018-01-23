<?php

namespace RA\NotificationsBundle\Model\Context;

class Context {

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var array $keys
     */
    private $keys;

    public function __construct( string $name, array $keys = [] )
    {
        $this->setName($name);
        $this->setKeys($keys);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @param array $keys
     */
    public function setKeys(array $keys = [])
    {
        $this->keys = $keys;
    }


}