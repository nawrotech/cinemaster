<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519112822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX unique_active_price_tier
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_tier ADD type VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_tier DROP name
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_active_price_tier ON price_tier (type, price, cinema_id, is_active) WHERE (is_active = true)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX unique_active_price_tier
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_tier ADD name VARCHAR(30) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_tier DROP type
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_active_price_tier ON price_tier (name, price, cinema_id, is_active) WHERE (is_active = true)
        SQL);
    }
}
