<?php

namespace RA\NotificationsBundle\Model\Context;

use RA\NotificationsBundle\Model\Configuration\Configuration;

class ContextManager {

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     * @var array $contexts
     */
    private $contexts = [];

    public function __construct(
        array $android,
        array $ios,
        array $device,
        array $contexts = []
    )
    {
        $this->createContexts($contexts);

        $this->configuration = new Configuration([
            'android_server_key'    => $android['server_key'],
            'android_fcm_server'    => $android['fcm_server'],
            'ios_push_passphrase'   => $ios['push_passphrase'],
            'ios_push_certificate'  => $ios['push_certificate'],
            'ios_apns_server'       => $ios['apns_server'],
            'device_class_name'     => $device['class'],
            'contexts'              => $this->contexts,
        ]);
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    private function createContexts(array $contexts = [])
    {
        foreach ($contexts as $name => $keys) {
            array_push($this->contexts, new Context($name, $keys));
        }
    }

    /**
     * @param string $name
     * @return Context
     */
    public function getContext(string $name) : Context {
        $filtered = array_filter($this->contexts, function(Context $item) use($name) {
            return $item->getName() == $name;
        });

        return count($filtered) > 0 ? $filtered[0] : null;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getContexts() : array {
        return $this->contexts;
    }


}