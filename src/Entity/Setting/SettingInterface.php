<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Entity\Setting;

use DateTimeInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

interface SettingInterface extends ResourceInterface
{

    const STORAGE_TYPE_TEXT = 'text';
    const STORAGE_TYPE_BOOLEAN = 'boolean';
    const STORAGE_TYPE_INTEGER = 'integer';
    const STORAGE_TYPE_FLOAT = 'float';
    const STORAGE_TYPE_DATETIME = 'datetime';
    const STORAGE_TYPE_DATE = 'date';
    const STORAGE_TYPE_JSON = 'json';

    public function getId(): ?int;

    public function getValue();

    public function setValue($value): void;

    public function getVendor(): ?string;

    public function setVendor(?string $vendor): void;

    public function getPlugin(): ?string;

    public function setPlugin(?string $plugin): void;

    public function getPath(): ?string;

    public function setPath(?string $path): void;

    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;

    public function getLocaleCode(): ?string;

    public function setLocaleCode(?string $localeCode): void;

    public function getStorageType(): ?string;

    public function setStorageType(?string $storageType): void;

    public function setStorageTypeFromValue($value): void;

    public function getTextValue(): ?string;

    public function setTextValue(?string $textValue): void;

    public function getBooleanValue(): ?bool;

    public function setBooleanValue(?bool $booleanValue): void;

    public function getIntegerValue(): ?int;

    public function setIntegerValue(?int $integerValue): void;

    public function getFloatValue(): ?float;

    public function setFloatValue(?float $floatValue): void;

    public function getDatetimeValue(): ?DateTimeInterface;

    public function setDatetimeValue(?DateTimeInterface $datetimeValue): void;

    public function getDateValue(): ?DateTimeInterface;

    public function setDateValue(?DateTimeInterface $dateValue): void;

    public function getJsonValue(): ?array;

    public function setJsonValue(?array $jsonValue): void;
}
