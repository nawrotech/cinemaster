<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114165617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE working_hours (id INT AUTO_INCREMENT NOT NULL, open_time TIME NOT NULL, close_time TIME NOT NULL, day_of_the_week INT NOT NULL, cinema_id INT DEFAULT NULL, INDEX IDX_D72CDC3DB4CB84B6 (cinema_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE working_hours ADD CONSTRAINT FK_D72CDC3DB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE working_hours DROP FOREIGN KEY FK_D72CDC3DB4CB84B6');
        $this->addSql('DROP TABLE working_hours');
    }
}
