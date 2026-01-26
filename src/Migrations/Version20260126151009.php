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

namespace MonsieurBiz\SyliusSettingsPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260126151009 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE mbiz_settings_setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE mbiz_settings_setting (id INT NOT NULL, channel_id INT DEFAULT NULL, vendor VARCHAR(255) NOT NULL, plugin VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, locale_code VARCHAR(5) DEFAULT NULL, storage_type VARCHAR(10) NOT NULL, text_value TEXT DEFAULT NULL, boolean_value BOOLEAN DEFAULT NULL, integer_value INT DEFAULT NULL, float_value DOUBLE PRECISION DEFAULT NULL, datetime_value TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_value DATE DEFAULT NULL, json_value JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_404A67E772F5A1AA ON mbiz_settings_setting (channel_id)');
        $this->addSql('COMMENT ON COLUMN mbiz_settings_setting.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE mbiz_settings_setting ADD CONSTRAINT FK_404A67E772F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE mbiz_settings_setting_id_seq CASCADE');
        $this->addSql('ALTER TABLE mbiz_settings_setting DROP CONSTRAINT FK_404A67E772F5A1AA');
        $this->addSql('DROP TABLE mbiz_settings_setting');
    }
}
