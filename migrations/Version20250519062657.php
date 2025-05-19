<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519062657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ADD price_tier_price DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ADD price_tier_color VARCHAR(7) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ADD price_tier_name VARCHAR(30) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ADD original_price_tier_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ADD CONSTRAINT FK_2B65FB0E713E8DF FOREIGN KEY (original_price_tier_id) REFERENCES price_tier (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B65FB0E713E8DF ON reservation_seat (original_price_tier_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat DROP CONSTRAINT FK_2B65FB0E713E8DF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2B65FB0E713E8DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat DROP price_tier_price
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat DROP price_tier_color
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat DROP price_tier_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat DROP original_price_tier_id
        SQL);
    }
}
