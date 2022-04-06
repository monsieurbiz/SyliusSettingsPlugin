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

namespace MonsieurBiz\SyliusSettingsPlugin\Entity\Setting;

use DateTimeInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface SettingInterface extends ResourceInterface
{
    public const STORAGE_TYPE_TEXT = 'text';

    public const STORAGE_TYPE_BOOLEAN = 'boolean';

    public const STORAGE_TYPE_INTEGER = 'integer';

    public const STORAGE_TYPE_FLOAT = 'float';

    public const STORAGE_TYPE_DATETIME = 'datetime';

    public const STORAGE_TYPE_DATE = 'date';

    public const STORAGE_TYPE_JSON = 'json';

    public function getId(): ?int;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     */
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

    /**
     * @param mixed $value
     */
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
