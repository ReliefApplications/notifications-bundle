<?php

namespace RA\NotificationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ra_notifications');

        $this->addConfiguration($rootNode);
        $this->addEntityManagementSection($rootNode);
        $this->addContext($rootNode);

        return $treeBuilder;
    }

    private function addContext(ArrayNodeDefinition $node){
        $node
            ->children()
                ->arrayNode('contexts')
                    ->canBeUnset()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('android')
                                ->children()
                                    ->scalarNode('server_key')->end()
                                    ->scalarNode('fcm_server')->end()
                                ->end()
                            ->end()
                            ->arrayNode('ios')
                                ->children()
                                    ->scalarNode('push_certificate')->end()
                                    ->scalarNode('push_passphrase')->end()
                                    ->scalarNode('apns_server')->end()
                                    ->scalarNode('apns_topic')->end()
                                    ->enumNode('protocol')->values(array('legacy', 'http2'))->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('android')
                    ->children()
                        ->scalarNode('server_key')->end()
                        ->scalarNode('fcm_server')->defaultValue('fcm.googleapis.com')->end()
                    ->end()
                ->end()
                ->arrayNode('ios')
                    ->children()
                        ->scalarNode('push_certificate')->end()
                        ->scalarNode('push_passphrase')->end()
                        ->scalarNode('apns_server')->defaultValue('api.push.apple.com')->end()
                        ->scalarNode('apns_topic')->defaultValue('reliefapps_notification')->end()
                        ->enumNode('protocol')->values(array('legacy', 'http2'))->defaultValue('http2')->end()
                    ->end()
                ->end()
            ->end();

    }

    private function addEntityManagementSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('device')->addDefaultsIfNotSet()->canBeUnset()
                    ->children()
                        ->scalarNode('class')->end()
                        ->scalarNode('manager')->defaultValue('@ra_notifications.device.manager')->end()
                    ->end()
                ->end()
            ->end();

    }
}
