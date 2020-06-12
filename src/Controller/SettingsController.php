<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Controller;

use MonsieurBiz\SyliusSettingsPlugin\Form\MainSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

final class SettingsController extends AbstractController
{
    /**
     * @var FormFactoryInterface
     */
    private FormFactoryInterface $formFactory;

    /**
     * SettingsController constructor.
     *
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param RegistryInterface $registry
     *
     * @return Response
     */
    public function indexAction(RegistryInterface $registry)
    {
        return $this->render('@MonsieurBizSyliusSettingsPlugin/Crud/index.html.twig', [
            'settings' => $registry->getAllSettings(),
        ]);
    }

    /**
     * @param RegistryInterface $registry
     * @param $alias
     *
     * @return Response
     */
    public function formAction(RegistryInterface $registry, $alias)
    {
        if (null === ($settings = $registry->getByAlias($alias))) {
            throw $this->createNotFoundException();
        }

        return $this->render('@MonsieurBizSyliusSettingsPlugin/Crud/edit.html.twig', [
            'settings' => $settings,
            'form_event' => 'monsieurbiz.settings.form',
            'form_event_dedicated' => sprintf('monsieurbiz.settings.form.%s', $settings->getAlias()),
            'form' => $this->getForm($settings)->createView(),
        ]);
    }

    /**
     * @param SettingsInterface $settings
     *
     * @return FormInterface
     */
    private function getForm(SettingsInterface $settings): FormInterface
    {
        return $this->formFactory->create(
            MainSettingsType::class,
            $settings
        );
    }
}
