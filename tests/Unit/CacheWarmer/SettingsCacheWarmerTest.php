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

namespace MonsieurBiz\SyliusSettingsPlugin\Tests\Unit\CacheWarmer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Deprecations\Deprecation;
use Doctrine\ORM\EntityManagerInterface;
use MonsieurBiz\SyliusSettingsPlugin\CacheWarmer\SettingsCacheWarmer;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

final class SettingsCacheWarmerTest extends TestCase
{
    public function testItSkipsWarmingWhenTheSettingsTableDoesNotExist(): void
    {
        $registry = $this->createMock(RegistryInterface::class);
        $registry->expects(self::never())->method('getAllSettings');

        $warmer = $this->createWarmer($this->createConnection(), $registry);

        self::assertSame([], $warmer->warmUp(sys_get_temp_dir()));
    }

    public function testItWarmsTheSettingsWhenTheSettingsTableExists(): void
    {
        $connection = $this->createConnection();
        $connection->executeStatement('CREATE TABLE mbiz_settings_setting (id INTEGER PRIMARY KEY)');

        $registry = $this->createMock(RegistryInterface::class);
        $registry->expects(self::once())->method('getAllSettings')->willReturn([]);

        $warmer = $this->createWarmer($connection, $registry);

        self::assertSame([], $warmer->warmUp(sys_get_temp_dir()));
    }

    /**
     * `Connection::getSchemaManager()` and passing a string to `tablesExist()` are deprecated in DBAL 3 and fail with DBAL 4.
     */
    public function testItDoesNotUseTheSchemaManagerApiRemovedInDbal4(): void
    {
        $identifiers = [
            'https://github.com/doctrine/dbal/issues/4515',
            'https://github.com/doctrine/dbal/issues/3580',
        ];
        $warmer = $this->createWarmer($this->createConnection(), $this->createMock(RegistryInterface::class));

        Deprecation::enableTrackingDeprecations();
        $before = array_intersect_key(Deprecation::getTriggeredDeprecations(), array_flip($identifiers));

        $warmer->warmUp(sys_get_temp_dir());

        self::assertSame($before, array_intersect_key(Deprecation::getTriggeredDeprecations(), array_flip($identifiers)));
    }

    private function createConnection(): Connection
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeQuery('SELECT 1');

        return $connection;
    }

    private function createWarmer(Connection $connection, RegistryInterface $registry): SettingsCacheWarmer
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        return new SettingsCacheWarmer(
            $this->createMock(ChannelRepositoryInterface::class),
            $registry,
            $entityManager,
        );
    }
}
