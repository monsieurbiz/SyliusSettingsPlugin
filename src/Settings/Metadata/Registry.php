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

use InvalidArgumentException;
use MonsieurBiz\SyliusSettingsPlugin\Settings\Metadata;
use MonsieurBiz\SyliusSettingsPlugin\Settings\MetadataInterface;

final class Registry implements RegistryInterface
{
    /**
     * @var array|MetadataInterface[]
     */
    private array $metadata = [];

    /**
     * @inheritdoc
     */
    public function get(string $alias): MetadataInterface
    {
        if (!\array_key_exists($alias, $this->metadata)) {
            throw new InvalidArgumentException(sprintf('Resource "%s" does not exist.', $alias));
        }

        return $this->metadata[$alias];
    }

    /**
     * @inheritdoc
     */
    public function add(MetadataInterface $metadata): void
    {
        $this->metadata[$metadata->getAlias()] = $metadata;
    }

    /**
     * @inheritdoc
     */
    public function addFromAliasAndConfiguration(string $alias, array $configuration): void
    {
        $this->add(Metadata::fromAliasAndConfiguration($alias, $configuration));
    }
}
