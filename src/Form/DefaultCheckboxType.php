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

namespace MonsieurBiz\SyliusSettingsPlugin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DefaultCheckboxType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['related_form_child'] = $options['related_form_child'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'related_form_child',
        ])->setAllowedTypes('related_form_child', [FormBuilder::class]);
    }

    public function getBlockPrefix()
    {
        return 'default_checkbox';
    }

    public function getParent(): string
    {
        return CheckboxType::class;
    }
}
