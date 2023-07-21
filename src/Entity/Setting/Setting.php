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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use LogicException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="mbiz_settings_setting")
 */
class Setting implements SettingInterface
{
    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $vendor;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $plugin;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $path;

    /**
     * @ORM\ManyToOne(targetEntity="\Sylius\Component\Core\Model\ChannelInterface")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     *
     * @Assert\Type(type="\Sylius\Component\Core\Model\ChannelInterface")
     */
    private ?ChannelInterface $channel;

    /**
     * @ORM\Column(name="locale_code", type="string", length=5, nullable=true)
     */
    private ?string $localeCode;

    /**
     * @ORM\Column(name="storage_type", type="string", length=10, nullable=false)
     */
    private ?string $storageType;

    /**
     * @ORM\Column(name="text_value", type="text", length=65535, nullable=true)
     */
    private ?string $textValue;

    /**
     * @ORM\Column(name="boolean_value", type="boolean", nullable=true)
     */
    private ?bool $booleanValue;

    /**
     * @ORM\Column(name="integer_value", type="integer", nullable=true)
     */
    private ?int $integerValue;

    /**
     * @ORM\Column(name="float_value", type="float", nullable=true)
     */
    private ?float $floatValue;

    /**
     * @ORM\Column(name="datetime_value", type="datetime", nullable=true)
     */
    private ?DateTimeInterface $datetimeValue;

    /**
     * @ORM\Column(name="date_value", type="date", nullable=true)
     */
    private ?DateTimeInterface $dateValue;

    /**
     * @ORM\Column(name="json_value", type="json", nullable=true)
     */
    private ?array $jsonValue;

    /**
     * @var DateTimeInterface|null
     *
     * @ORM\Column(name="created_at", type="datetime_immutable")
     *
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var DateTimeInterface|null
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
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
            'double' => function (): string {
                return SettingInterface::STORAGE_TYPE_FLOAT;
            },
            'array' => function (): string {
                return SettingInterface::STORAGE_TYPE_JSON;
            },
            'object' => function (object $value): string {
                if ($value instanceof DateTimeInterface) {
                    return SettingInterface::STORAGE_TYPE_DATETIME;
                }
                if ($value instanceof JsonSerializable) {
                    return SettingInterface::STORAGE_TYPE_JSON;
                }

                throw new LogicException('Impossible to match the type of the value.');
            },
            'string' => function (): string {
                return SettingInterface::STORAGE_TYPE_TEXT;
            },
            'boolean' => function (): string {
                return SettingInterface::STORAGE_TYPE_BOOLEAN;
            },
            'integer' => function (): string {
                return SettingInterface::STORAGE_TYPE_INTEGER;
            },
            'NULL' => function (): string {
                return SettingInterface::STORAGE_TYPE_TEXT;
            },
        ];

        $type = \gettype($value);
        if (!isset($types[$type])) {
            throw new LogicException(sprintf('Impossible to match the type of the value. (%s)', $type));
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
}
