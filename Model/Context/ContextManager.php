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
        $android_server_key,
        $android_fcm_server,
        $ios_push_passphrase,
        $ios_push_certificate,
        $ios_apns_server,
        $ios_apns_topic,
        $ios_protocol,
        $device_class,
        $device_manager,
        array $contexts = []
    )
    {
        $this->createContexts($contexts);

        $this->configuration = new Configuration([
            'android_server_key'    => $android_server_key ?: '',
            'android_fcm_server'    => $android_fcm_server ?: '',
            'ios_push_passphrase'   => $ios_push_passphrase ?: '',
            'ios_push_certificate'  => $ios_push_certificate ?: '',
            'ios_apns_server'       => $ios_apns_server ?: '',
            'ios_apns_topic'        => $ios_apns_topic ?: '',
            'ios_protocol'          => $ios_protocol ?: '',
            'device_class'          => $device_class ?: '',
            'device_manager'        => $device_manager ?: '',
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
     * @return mixed
     */
    public function getContext(string $name)
    {
        $filtered = array_filter($this->contexts, function(Context $item) use($name) {
            return $item->getName() == $name;
        });

        return count($filtered) > 0 ? current($filtered) : null;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getContexts() : array
    {
        return $this->contexts;
    }

    /**
     * Guessing the context consists on returning the first context found
     * @return Context
     */
    public function guessContext() : Context
    {
        return (count($this->contexts)) ? $this->contexts[0] : null;
    }


}