<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022114639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screening_room ADD screening_room_setup_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE screening_room ADD CONSTRAINT FK_56558C8DDBB9A999 FOREIGN KEY (screening_room_setup_id) REFERENCES screening_room_setup (id)');
        $this->addSql('CREATE INDEX IDX_56558C8DDBB9A999 ON screening_room (screening_room_setup_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screening_room DROP FOREIGN KEY FK_56558C8DDBB9A999');
        $this->addSql('DROP INDEX IDX_56558C8DDBB9A999 ON screening_room');
        $this->addSql('ALTER TABLE screening_room DROP screening_room_setup_id');
    }
}
