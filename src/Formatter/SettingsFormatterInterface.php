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

namespace MonsieurBiz\SyliusSettingsPlugin\Formatter;

interface SettingsFormatterInterface
{
    /**
     * @param int|float|string|array $value
     * @param mixed $type
     *
     * @return mixed
     */
    public function formatValue($type, $value);
}
