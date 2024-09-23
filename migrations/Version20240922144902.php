<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240922144902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cinema DROP created_at, DROP updated_at, CHANGE slug slug VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE screening_room DROP created_at, DROP updated_at, CHANGE slug slug VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cinema ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, CHANGE slug slug VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE screening_room ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, CHANGE slug slug VARCHAR(128) NOT NULL');
    }
}
