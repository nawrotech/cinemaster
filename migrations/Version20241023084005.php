<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023084005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screening_room DROP FOREIGN KEY FK_56558C8DDBB9A999');
        $this->addSql('ALTER TABLE screening_room ADD CONSTRAINT FK_56558C8DDBB9A999 FOREIGN KEY (screening_room_setup_id) REFERENCES screening_room_setup (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screening_room DROP FOREIGN KEY FK_56558C8DDBB9A999');
        $this->addSql('ALTER TABLE screening_room ADD CONSTRAINT FK_56558C8DDBB9A999 FOREIGN KEY (screening_room_setup_id) REFERENCES screening_room_setup (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
