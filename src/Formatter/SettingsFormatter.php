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

namespace MonsieurBiz\SyliusSettingsPlugin\Formatter;

use DateTime;
use MonsieurBiz\SyliusSettingsPlugin\Entity\Setting\SettingInterface;

class SettingsFormatter implements SettingsFormatterInterface
{
    /**
     * @param int|float|string|array $value
     * @param mixed $type
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function formatValue($type, $value)
    {
        switch ($type) {
            case SettingInterface::STORAGE_TYPE_BOOLEAN:
                $value = (bool) $value;

                break;
            case SettingInterface::STORAGE_TYPE_INTEGER:
                $value = (int) $value;

                break;
            case SettingInterface::STORAGE_TYPE_FLOAT:
                $value = (float) $value;

                break;
            case SettingInterface::STORAGE_TYPE_JSON:
                if (!\is_array($value)) {
                    $value = json_decode((string) $value, true);
                }

                break;
            case SettingInterface::STORAGE_TYPE_DATE:
            case SettingInterface::STORAGE_TYPE_DATETIME:
                if (\is_int($value)) {
                    $value = (new DateTime())->setTimestamp($value);

                    break;
                }

                /** @phpstan-ignore-next-line */
                $value = new DateTime((string) $value);

                break;
        }

        return $value;
    }
}
