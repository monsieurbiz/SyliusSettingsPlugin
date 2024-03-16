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

use MonsieurBiz\SyliusSettingsPlugin\Settings\Settings;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractSettingsExtension extends AbstractTypeExtension implements SettingsExtensionInterface
{
    public function addWithDefaultCheckbox(FormBuilderInterface $builder, string $child, string $type = null, array $options = []): self
    {
        $data = (array) $builder->getData();
        $builder->add($child, $type, $options);
        if (!$this->isDefaultForm($builder)) {
            $builder->add($child . '___' . Settings::DEFAULT_KEY, DefaultCheckboxType::class, [
                'label' => 'monsieurbiz.settings.ui.use_default_value',
                'related_form_child' => $builder->get($child),
                'data' => !\array_key_exists($child, $data),
                'required' => true,
            ]);
        }

        return $this;
    }

    public function removeWithDefaultCheckbox(FormBuilderInterface $builder, string $child): self
    {
        $builder->remove($child);
        if (!$this->isDefaultForm($builder)) {
            $builder->remove($child . '___' . Settings::DEFAULT_KEY);
        }

        return $this;
    }

    protected function isDefaultForm(FormBuilderInterface $builder): bool
    {
        return !$builder->getOption('show_default_checkboxes', true);
    }
}
