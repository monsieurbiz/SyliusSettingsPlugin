<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Settings;

class Settings implements SettingsInterface
{

    /**
     * @var Metadata
     */
    private Metadata $metadata;

    /**
     * Settings constructor.
     *
     * @param Metadata $metadata
     */
    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getAlias(): string
    {
        return $this->metadata->getAlias();
    }

    public function getVendorName(): string
    {
        return $this->metadata->getParameter('vendor_name');
    }

    public function getVendorUrl(): ?string
    {
        return $this->metadata->getParameter('vendor_url');
    }

    public function getPluginName(): string
    {
        return $this->metadata->getParameter('plugin_name');
    }

    public function getDescription(): string
    {
        return $this->metadata->getParameter('description');
    }

    public function getIcon(): string
    {
        return $this->metadata->getParameter('icon');
    }

//    public function getForm(): SettingsTypeInterface;
//    public function getConfig($path, ?ChannelInterface $channel = null, ?string $localeCode = null);
}
