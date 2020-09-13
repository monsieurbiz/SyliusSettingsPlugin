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

namespace MonsieurBiz\SyliusSettingsPlugin\Factory\Form;

use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Symfony\Component\Form\FormInterface;

interface MainSettingsFormTypeFactoryInterface
{
    public function createNew(SettingsInterface $settings, string $type, array $options = []): FormInterface;
}
