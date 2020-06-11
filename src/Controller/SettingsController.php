<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Controller;

use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SettingsController extends AbstractController
{
    public function indexAction(RegistryInterface $registry)
    {
        return $this->render('@MonsieurBizSyliusSettingsPlugin/index.html.twig', [
            'settings' => $registry->getAllSettings(),
        ]);
    }
}
