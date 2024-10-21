<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021092140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visual_format ADD cinema_id INT NOT NULL');
        $this->addSql('ALTER TABLE visual_format ADD CONSTRAINT FK_A187A35DB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
        $this->addSql('CREATE INDEX IDX_A187A35DB4CB84B6 ON visual_format (cinema_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visual_format DROP FOREIGN KEY FK_A187A35DB4CB84B6');
        $this->addSql('DROP INDEX IDX_A187A35DB4CB84B6 ON visual_format');
        $this->addSql('ALTER TABLE visual_format DROP cinema_id');
    }
}
