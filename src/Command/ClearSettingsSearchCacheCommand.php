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

namespace MonsieurBiz\SyliusSettingsPlugin\Command;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'monsieurbiz:settings:search-cache:clear', description: 'Clears the settings search cache pool.')]
final class ClearSettingsSearchCacheCommand extends Command
{
    public function __construct(
        private CacheItemPoolInterface $settingsSearchCache,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        if (!$this->settingsSearchCache->clear()) {
            $symfonyStyle->error('Unable to clear the settings search cache.');

            return Command::FAILURE;
        }

        $symfonyStyle->success('Settings search cache cleared.');

        return Command::SUCCESS;
    }
}
