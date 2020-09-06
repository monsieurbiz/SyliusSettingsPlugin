<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Repository;

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

        // The order is primordial! Default first in the results.
        $queryBuilder->addOrderBy('o.localeCode', 'ASC');
        $queryBuilder->addOrderBy('o.channel', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }
}
