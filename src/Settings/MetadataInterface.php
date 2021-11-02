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

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

interface MetadataInterface
{
    public static function fromAliasAndConfiguration(string $alias, array $parameters): self;

    public function getAlias(): string;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getApplicationName(bool $aliased = false): string;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getName(bool $aliased = false): string;

    /**
     * @return mixed
     */
    public function getParameter(string $name);

    public function hasParameter(string $name): bool;

    public function getParameters(): array;

    public function getDefaultValues(): array;

    public function getClass(string $name): string;

    public function hasClass(string $name): bool;

    public function getServiceId(string $serviceName): string;

    public function useLocales(): bool;
}
