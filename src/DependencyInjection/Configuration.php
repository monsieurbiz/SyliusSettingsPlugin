<?php

/*
 * This file is part of Monsieur Biz' Settings plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\DependencyInjection;

use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('monsieurbiz_sylius_settings');
        $rootNode = $this->getRootNode($treeBuilder);

        $this->addPlugins($rootNode);

        return $treeBuilder;
    }

    private function addPlugins(ArrayNodeDefinition $rootNode): void
    {
        /** @scrutinizer ignore-call */
        $rootNode
                ->children()
                ->arrayNode('plugins')
                    ->useAttributeAsKey('name', false)
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('vendor_name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('vendor_url')->defaultNull()->end()
                            ->scalarNode('plugin_name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('description')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('icon')->isRequired()->cannotBeEmpty()->end()
                            ->booleanNode('use_locales')->end()
                            ->arrayNode('classes')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('settings')->defaultValue(Settings::class)->end()
                                    ->scalarNode('form')->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                            ->arrayNode('default_values')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;
    }

    private function getRootNode(TreeBuilder $treeBuilder): ArrayNodeDefinition
    {
        if (method_exists($treeBuilder, 'getRootNode')) {
            return $treeBuilder->getRootNode();
        }

        return /** @scrutinizer ignore-deprecated */ $treeBuilder->root('monsieurbiz_sylius_settings');
    }
}
