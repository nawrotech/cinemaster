<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519063032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE price_tier ALTER color TYPE VARCHAR(7)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_tier ALTER color SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_price TYPE DOUBLE PRECISION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_price SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_color TYPE VARCHAR(7)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_color SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_name TYPE VARCHAR(30)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_name SET NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_price TYPE DOUBLE PRECISION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_price DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_color TYPE VARCHAR(7)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_color DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_name TYPE VARCHAR(30)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_seat ALTER price_tier_name DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_tier ALTER color TYPE VARCHAR(7)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_tier ALTER color DROP NOT NULL
        SQL);
    }
}
