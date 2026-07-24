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

namespace MonsieurBiz\SyliusSettingsPlugin\Tests\Unit\Command;

use BadMethodCallException;
use MonsieurBiz\SyliusSettingsPlugin\Command\ClearSettingsSearchCacheCommand;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ClearSettingsSearchCacheCommandTest extends TestCase
{
    public function testItClearsOnlyInjectedSearchCachePool(): void
    {
        $searchCache = new SpyCacheItemPool();
        $settingsValueCache = new SpyCacheItemPool();
        $commandTester = new CommandTester(new ClearSettingsSearchCacheCommand($searchCache));

        $exitCode = $commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame(1, $searchCache->clearCalls);
        self::assertSame(0, $settingsValueCache->clearCalls);
        self::assertStringContainsString('Settings search cache cleared.', $commandTester->getDisplay());
    }

    public function testItReturnsFailureWhenPoolCannotBeCleared(): void
    {
        $searchCache = new SpyCacheItemPool(false);
        $commandTester = new CommandTester(new ClearSettingsSearchCacheCommand($searchCache));

        $exitCode = $commandTester->execute([]);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertSame(1, $searchCache->clearCalls);
        self::assertStringContainsString('Unable to clear the settings search cache.', $commandTester->getDisplay());
    }
}

final class SpyCacheItemPool implements CacheItemPoolInterface
{
    public int $clearCalls = 0;

    public function __construct(private bool $clearResult = true)
    {
    }

    public function getItem(string $key): CacheItemInterface
    {
        throw new BadMethodCallException('Not used.');
    }

    public function getItems(array $keys = []): iterable
    {
        return [];
    }

    public function hasItem(string $key): bool
    {
        return false;
    }

    public function clear(): bool
    {
        ++$this->clearCalls;

        return $this->clearResult;
    }

    public function deleteItem(string $key): bool
    {
        return true;
    }

    public function deleteItems(array $keys): bool
    {
        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return true;
    }

    public function commit(): bool
    {
        return true;
    }
}
