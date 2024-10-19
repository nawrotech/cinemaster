<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241009083136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_seat (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(15) NOT NULL, showtime_id INT NOT NULL, seat_id INT NOT NULL, INDEX IDX_2B65FB0E28BE1523 (showtime_id), INDEX IDX_2B65FB0EC1DAFE35 (seat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE reservation_seat ADD CONSTRAINT FK_2B65FB0E28BE1523 FOREIGN KEY (showtime_id) REFERENCES showtime (id)');
        $this->addSql('ALTER TABLE reservation_seat ADD CONSTRAINT FK_2B65FB0EC1DAFE35 FOREIGN KEY (seat_id) REFERENCES screening_room_seat (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_seat DROP FOREIGN KEY FK_2B65FB0E28BE1523');
        $this->addSql('ALTER TABLE reservation_seat DROP FOREIGN KEY FK_2B65FB0EC1DAFE35');
        $this->addSql('DROP TABLE reservation_seat');
    }
}
