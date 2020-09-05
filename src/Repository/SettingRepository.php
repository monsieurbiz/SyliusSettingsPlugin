<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSettingsPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;

final class SettingRepository extends EntityRepository implements SettingRepositoryInterface
{
    public function findAllByChannelAndLocaleWithDefault(string $vendor, string $plugin, ChannelInterface $channel = null, LocaleInterface $locale = null): array
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
        if (null === $locale) {
            $queryBuilder->andWhere('o.localeCode IS NULL');
        } else {
            $queryBuilder
                ->andWhere('o.localeCode = :localeCode')
                ->setParameter('localeCode', $locale->getCode())
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
