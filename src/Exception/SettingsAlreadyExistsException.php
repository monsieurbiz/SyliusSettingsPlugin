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

namespace MonsieurBiz\SyliusSettingsPlugin\Exception;

final class SettingsAlreadyExistsException extends SettingsException
{
    public function __construct(SettingsInterface $settings)
    {
        parent::__construct(
            sprintf("Settings instance aliased '%s' already exists.", $settings->getAlias())
        );
    }
}
