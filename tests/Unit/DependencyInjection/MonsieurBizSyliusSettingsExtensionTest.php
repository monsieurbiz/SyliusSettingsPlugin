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

namespace MonsieurBiz\SyliusSettingsPlugin\Tests\Unit\DependencyInjection;

use MonsieurBiz\SyliusSettingsPlugin\DependencyInjection\MonsieurBizSyliusSettingsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MonsieurBizSyliusSettingsExtensionTest extends TestCase
{
    public function testItPrependsSearchCachePoolWhenDoctrineMigrationsPrependIsDisabled(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('sylius_core.prepend_doctrine_migrations', false);

        (new MonsieurBizSyliusSettingsExtension())->prepend($container);

        $frameworkConfigs = $container->getExtensionConfig('framework');
        $doctrineMigrationsConfigs = $container->getExtensionConfig('doctrine_migrations');

        self::assertSame([], $doctrineMigrationsConfigs);
        self::assertArrayHasKey('cache', $frameworkConfigs[0]);
        self::assertSame([
            'adapter' => 'cache.adapter.array',
            'public' => false,
            'tags' => true,
        ], $frameworkConfigs[0]['cache']['pools']['monsieurbiz_settings.cache']);
        self::assertSame([
            'adapter' => 'cache.adapter.array',
            'public' => false,
        ], $frameworkConfigs[0]['cache']['pools']['monsieurbiz_settings.search_cache']);
    }
}
