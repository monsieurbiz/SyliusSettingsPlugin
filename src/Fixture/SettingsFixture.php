<?php

/*
 * This file is part of Monsieur Biz' Settings plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use MonsieurBiz\SyliusSettingsPlugin\Fixture\Factory\SettingsFixtureFactory;
use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class SettingsFixture extends AbstractResourceFixture
{
    public function __construct(
        EntityManagerInterface $settingsManager,
        SettingsFixtureFactory $exampleFactory
    ) {
        parent::__construct($settingsManager, $exampleFactory);
    }

    public function getName(): string
    {
        return 'monsieurbiz_settings';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        /** @phpstan-ignore-next-line */
        $resourceNode
            ->children()
                ->scalarNode('alias')->cannotBeEmpty()->end()
                ->scalarNode('path')->cannotBeEmpty()->end()
                ->scalarNode('channel')->end()
                ->scalarNode('locale')->end()
                ->scalarNode('type')->cannotBeEmpty()->end()
                ->variableNode('value')->end()
                ->variableNode('ignore_if_exists')->end()
                ->end()
            ->end()
        ;
    }
}
