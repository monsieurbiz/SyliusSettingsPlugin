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

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

final class SettingRepository extends EntityRepository implements SettingRepositoryInterface
{
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
        $this->addChannelCondition($queryBuilder, $channel, false);

        // Manage Locale
        $this->addLocaleCondition($queryBuilder, $localeCode, false);

        return $queryBuilder->getQuery()->getResult();
    }

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
        $this->addChannelCondition($queryBuilder, $channel);

        // Manage Locale
        $this->addLocaleCondition($queryBuilder, $localeCode);

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

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function addChannelCondition(QueryBuilder $queryBuilder, ChannelInterface $channel = null, bool $withNull = true): void
    {
        if (null === $channel) {
            $queryBuilder->andWhere('o.channel IS NULL');

            return;
        }

        $whereCondition = 'o.channel = :channel';
        if ($withNull) {
            $whereCondition .= ' OR o.channel IS NULL';
        }
        $queryBuilder
            ->andWhere($whereCondition)
            ->setParameter('channel', $channel)
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function addLocaleCondition(QueryBuilder $queryBuilder, ?string $localeCode = null, bool $withNull = true): void
    {
        if (null === $localeCode) {
            $queryBuilder->andWhere('o.localeCode IS NULL');

            return;
        }

        $whereCondition = 'o.localeCode = :localeCode';
        if ($withNull) {
            $whereCondition .= ' OR o.localeCode IS NULL';
        }
        $queryBuilder
            ->andWhere($whereCondition)
            ->setParameter('localeCode', $localeCode)
        ;
    }
}
