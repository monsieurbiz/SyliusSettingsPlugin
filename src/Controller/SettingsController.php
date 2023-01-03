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

namespace MonsieurBiz\SyliusSettingsPlugin\Controller;

use MonsieurBiz\SyliusSettingsPlugin\Factory\Form\MainSettingsFormTypeFactoryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Form\MainSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Processor\SettingsProcessorInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SettingsController extends AbstractController
{
    private SettingsProcessorInterface $settingsProcessor;

    private MainSettingsFormTypeFactoryInterface $formFactory;

    /**
     * SettingsController constructor.
     */
    public function __construct(
        SettingsProcessorInterface $settingsProcessor,
        MainSettingsFormTypeFactoryInterface $formFactory
    ) {
        $this->settingsProcessor = $settingsProcessor;
        $this->formFactory = $formFactory;
    }

    /**
     * @return Response
     */
    public function indexAction(RegistryInterface $registry)
    {
        return $this->render('@MonsieurBizSyliusSettingsPlugin/Crud/index.html.twig', [
            'settings' => $registry->getAllSettings(),
        ]);
    }

    /**
     * @return Response
     */
    public function formAction(Request $request, RegistryInterface $registry, string $alias)
    {
        if (null === ($settings = $registry->getByAlias($alias))) {
            throw $this->createNotFoundException();
        }

        $form = $this->getForm($settings);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = (array) $form->getData();
            $this->settingsProcessor->processData($settings, $data);
            $this->addFlash('success', 'monsieurbiz.settings.settings_successfully_saved');

            return $this->redirectToRoute('monsieurbiz_sylius_settings_admin_edit', [
                'alias' => $settings->getAlias(),
            ]);
        }

        return $this->render(
            '@MonsieurBizSyliusSettingsPlugin/Crud/edit.html.twig',
            [
                'settings' => $settings,
                'form_event' => 'monsieurbiz.settings.form',
                'form_event_dedicated' => sprintf('monsieurbiz.settings.form.%s', $settings->getAlias()),
                'form' => $form->createView(),
            ]
        );
    }

    private function getForm(SettingsInterface $settings): FormInterface
    {
        return $this->formFactory->createNew(
            $settings,
            MainSettingsType::class,
            [
                'action' => $this->generateUrl('monsieurbiz_sylius_settings_admin_edit_post', ['alias' => $settings->getAlias()]),
                'method' => 'POST',
                'settings' => $settings,
            ],
        );
    }
}
