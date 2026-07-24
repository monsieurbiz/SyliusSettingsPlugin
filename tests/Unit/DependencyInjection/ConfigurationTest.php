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

use MonsieurBiz\SyliusSettingsPlugin\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testItAcceptsPluginCategory(): void
    {
        $config = $this->processConfiguration([
            'plugins' => [
                'app.fake_blog' => $this->getPluginConfiguration([
                    'category' => 'Content',
                ]),
            ],
        ]);

        self::assertSame('Content', $config['plugins']['app.fake_blog']['category']);
    }

    public function testItKeepsPluginCategoryNullWhenMissing(): void
    {
        $config = $this->processConfiguration([
            'plugins' => [
                'app.fake_blog' => $this->getPluginConfiguration(),
            ],
        ]);

        self::assertNull($config['plugins']['app.fake_blog']['category']);
    }

    private function processConfiguration(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }

    private function getPluginConfiguration(array $config = []): array
    {
        return array_replace([
            'vendor_name' => 'Fake Fixtures',
            'vendor_url' => null,
            'plugin_name' => 'Fake Blog Settings',
            'description' => 'Blog configuration for testing.',
            'icon' => 'tabler:article',
            'use_locales' => true,
            'classes' => [
                'form' => 'App\\Form\\SettingsType',
            ],
            'default_values' => [
                'enabled' => true,
            ],
        ], $config);
    }
}
