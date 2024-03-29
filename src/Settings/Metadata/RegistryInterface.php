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

namespace MonsieurBiz\SyliusSettingsPlugin\Settings\Metadata;

use MonsieurBiz\SyliusSettingsPlugin\Settings\MetadataInterface;

interface RegistryInterface
{
    public function get(string $alias): MetadataInterface;

    public function add(MetadataInterface $metadata): void;

    public function addFromAliasAndConfiguration(string $alias, array $configuration): void;
}
