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
use JsonSerializable;
use LogicException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class Setting implements SettingInterface
{
    use TimestampableTrait;

    protected ?int $id;

    protected ?string $vendor;

    protected ?string $plugin;

    protected ?string $path;

    protected ?ChannelInterface $channel;

    protected ?string $localeCode;

    protected ?string $storageType = null;

    protected ?string $textValue;

    protected ?bool $booleanValue;

    protected ?int $integerValue;

    protected ?float $floatValue;

    protected ?DateTimeInterface $datetimeValue;

    protected ?DateTimeInterface $dateValue;

    protected ?array $jsonValue;

    /** @var DateTimeInterface|null */
    protected $createdAt;

    /** @var DateTimeInterface|null */
    protected $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (null === $this->getStorageType()) {
            return null;
        }
        $getter = 'get' . $this->getStorageType() . 'value';

        return $this->{$getter}();
    }

    public function setValue($value): void
    {
        if (null === $this->getStorageType()) {
            throw new LogicException('The storage type MUST be defined before setting the value using ' . __METHOD__ . '.');
        }
        $setter = 'set' . $this->getStorageType() . 'value';
        $this->{$setter}($value);
    }

    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    public function setVendor(?string $vendor): void
    {
        $this->vendor = $vendor;
    }

    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    public function setPlugin(?string $plugin): void
    {
        $this->plugin = $plugin;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    public function getLocaleCode(): ?string
    {
        return $this->localeCode;
    }

    public function setLocaleCode(?string $localeCode): void
    {
        $this->localeCode = $localeCode;
    }

    public function getStorageType(): ?string
    {
        return $this->storageType;
    }

    public function setStorageType(?string $storageType): void
    {
        $this->storageType = $storageType;
    }

    /**
     * @param mixed $value
     *
     * @throws LogicException
     */
    public function setStorageTypeFromValue($value): void
    {
        $this->setStorageType(
            $this->getTypeFromValue($value)
        );
    }

    /**
     * @param mixed $value
     */
    private function getTypeFromValue($value): string
    {
        $types = [
            'double' => static function (): string {
                return SettingInterface::STORAGE_TYPE_FLOAT;
            },
            'array' => static function (): string {
                return SettingInterface::STORAGE_TYPE_JSON;
            },
            'object' => static function (object $value): string {
                if ($value instanceof DateTimeInterface) {
                    return SettingInterface::STORAGE_TYPE_DATETIME;
                }
                if ($value instanceof JsonSerializable) {
                    return SettingInterface::STORAGE_TYPE_JSON;
                }

                throw new LogicException('Impossible to match the type of the value.');
            },
            'string' => static function (): string {
                return SettingInterface::STORAGE_TYPE_TEXT;
            },
            'boolean' => static function (): string {
                return SettingInterface::STORAGE_TYPE_BOOLEAN;
            },
            'integer' => static function (): string {
                return SettingInterface::STORAGE_TYPE_INTEGER;
            },
            'NULL' => static function (): string {
                return SettingInterface::STORAGE_TYPE_TEXT;
            },
        ];

        $type = \gettype($value);
        if (!isset($types[$type])) {
            throw new LogicException(\sprintf('Impossible to match the type of the value. (%s)', $type));
        }

        return $types[$type]($value); /** @phpstan-ignore-line */
    }

    public function getTextValue(): ?string
    {
        return $this->textValue;
    }

    public function setTextValue(?string $textValue): void
    {
        $this->textValue = $textValue;
    }

    public function getBooleanValue(): ?bool
    {
        return $this->booleanValue;
    }

    public function setBooleanValue(?bool $booleanValue): void
    {
        $this->booleanValue = $booleanValue;
    }

    public function getIntegerValue(): ?int
    {
        return $this->integerValue;
    }

    public function setIntegerValue(?int $integerValue): void
    {
        $this->integerValue = $integerValue;
    }

    public function getFloatValue(): ?float
    {
        return $this->floatValue;
    }

    public function setFloatValue(?float $floatValue): void
    {
        $this->floatValue = $floatValue;
    }

    public function getDatetimeValue(): ?DateTimeInterface
    {
        return $this->datetimeValue;
    }

    public function setDatetimeValue(?DateTimeInterface $datetimeValue): void
    {
        $this->datetimeValue = $datetimeValue;
    }

    public function getDateValue(): ?DateTimeInterface
    {
        return $this->dateValue;
    }

    public function setDateValue(?DateTimeInterface $dateValue): void
    {
        $this->dateValue = $dateValue;
    }

    public function getJsonValue(): ?array
    {
        return $this->jsonValue;
    }

    public function setJsonValue(?array $jsonValue): void
    {
        $this->jsonValue = $jsonValue;
    }

    public static function getAllStorageTypes(): array
    {
        return [
            SettingInterface::STORAGE_TYPE_TEXT,
            SettingInterface::STORAGE_TYPE_BOOLEAN,
            SettingInterface::STORAGE_TYPE_INTEGER,
            SettingInterface::STORAGE_TYPE_FLOAT,
            SettingInterface::STORAGE_TYPE_JSON,
            SettingInterface::STORAGE_TYPE_DATE,
            SettingInterface::STORAGE_TYPE_DATETIME,
        ];
    }
}
