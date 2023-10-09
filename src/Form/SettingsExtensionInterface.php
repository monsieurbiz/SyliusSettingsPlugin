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

namespace MonsieurBiz\SyliusSettingsPlugin\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;

interface SettingsExtensionInterface extends FormTypeExtensionInterface
{
    /**
     * This method should add a checkbox field to the form when adding a new setting's field.
     *
     * @return $this
     */
    public function addWithDefaultCheckbox(FormBuilderInterface $builder, string $child, string $type = null, array $options = []): self;
}
