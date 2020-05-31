<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('monsieurbiz_sylius_settings');
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('monsieurbiz_sylius_settings');
        }

        $this->addPlugins($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addPlugins(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('plugins')
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('vendor')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('plugin')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
