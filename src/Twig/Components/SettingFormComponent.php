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

namespace MonsieurBiz\SyliusSettingsPlugin\Twig\Components;

use MonsieurBiz\SyliusSettingsPlugin\Factory\Form\MainSettingsFormTypeFactoryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Form\MainSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Bundle\UiBundle\Twig\Component\LiveCollectionTrait;
use Sylius\Bundle\UiBundle\Twig\Component\TemplatePropTrait;
use Sylius\TwigHooks\LiveComponent\HookableLiveComponentTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

final class SettingFormComponent
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;
    use HookableLiveComponentTrait;
    use LiveCollectionTrait;
    use TemplatePropTrait;

    #[LiveProp(hydrateWith: 'hydrateSettings', dehydrateWith: 'dehydrateSettings')]
    public SettingsInterface $settings;

    #[LiveProp]
    public array $disabledInputs = [];

    public function __construct(
        private MainSettingsFormTypeFactoryInterface $formFactory,
        private RouterInterface $router,
        private RegistryInterface $registry,
    ) {
    }

    public function hydrateSettings(mixed $value): ?SettingsInterface
    {
        if (!\is_string($value)) {
            return null;
        }

        return $this->registry->getByAlias($value);
    }

    public function dehydrateSettings(mixed $value): ?string
    {
        if (!$value instanceof SettingsInterface) {
            return null;
        }

        return $value->getAlias();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->createNew(
            $this->settings,
            MainSettingsType::class,
            [
                'action' => $this->router->generate('monsieurbiz_sylius_settings_admin_edit_post', ['alias' => $this->settings->getAlias()]),
                'method' => 'POST',
                'settings' => $this->settings,
                'disabled_inputs' => $this->disabledInputs,
            ],
        );
    }
}
