<?php

/*
 * This file is part of Monsieur Biz' Settings plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

final class SettingRepository extends EntityRepository implements SettingRepositoryInterface
{
    /**
     * @param string $vendor
     * @param string $plugin
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     *
     * @return array
     */
    public function findAllByChannelAndLocale(string $vendor, string $plugin, ChannelInterface $channel = null, ?string $localeCode = null): array
    {
        $queryBuilder = $this->createQueryBuilder('o');

        $queryBuilder
            ->andWhere('o.vendor = :vendor')
            ->andWhere('o.plugin = :plugin')
            ->setParameter('vendor', $vendor)
            ->setParameter('plugin', $plugin)
        ;

        // Manage Channel
        if (null === $channel) {
            $queryBuilder->andWhere('o.channel IS NULL');
        } else {
            $queryBuilder
                ->andWhere('o.channel = :channel')
                ->setParameter('channel', $channel)
            ;
        }

        // Manage Locale
        if (null === $localeCode) {
            $queryBuilder->andWhere('o.localeCode IS NULL');
        } else {
            $queryBuilder
                ->andWhere('o.localeCode = :localeCode')
                ->setParameter('localeCode', $localeCode)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param string $vendor
     * @param string $plugin
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     *
     * @return array
     */
    public function findAllByChannelAndLocaleWithDefault(string $vendor, string $plugin, ChannelInterface $channel = null, ?string $localeCode = null): array
    {
        $queryBuilder = $this->createQueryBuilder('o');

        $queryBuilder
            ->andWhere('o.vendor = :vendor')
            ->andWhere('o.plugin = :plugin')
            ->setParameter('vendor', $vendor)
            ->setParameter('plugin', $plugin)
        ;

        // Manage Channel
        if (null === $channel) {
            $queryBuilder->andWhere('o.channel IS NULL');
        } else {
            $queryBuilder
                ->andWhere('o.channel = :channel OR o.channel IS NULL')
                ->setParameter('channel', $channel)
            ;
        }

        // Manage Locale
        if (null === $localeCode) {
            $queryBuilder->andWhere('o.localeCode IS NULL');
        } else {
            $queryBuilder
                ->andWhere('o.localeCode = :localeCode OR o.localeCode IS NULL')
                ->setParameter('localeCode', $localeCode)
            ;
        }

        // The order is primordial! Default first in the results, correct value last
        $queryBuilder->addSelect(<<<EXPR
            CASE WHEN
                o.channel IS NOT NULL
                THEN
                    (CASE WHEN
                        o.localeCode IS NULL
                        THEN 2
                        ELSE 1
                    END)
                ELSE
                    (CASE WHEN
                        o.localeCode IS NULL
                        THEN 4
                        ELSE 3
                    END)
            END AS value_position
        EXPR);
        $queryBuilder->addOrderBy('value_position', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
}
