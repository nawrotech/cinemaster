<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020092113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cinema (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(100) NOT NULL, max_rows INT NOT NULL, max_seats_per_row INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, street_name VARCHAR(50) NOT NULL, building_number VARCHAR(10) NOT NULL, postal_code VARCHAR(6) NOT NULL, city VARCHAR(25) NOT NULL, district VARCHAR(30) NOT NULL, country VARCHAR(50) NOT NULL, owner_id INT NOT NULL, UNIQUE INDEX UNIQ_D48304B45E237E06 (name), UNIQUE INDEX UNIQ_D48304B4989D9B62 (slug), INDEX IDX_D48304B47E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE format (id INT AUTO_INCREMENT NOT NULL, audio_version VARCHAR(255) NOT NULL, visual_version VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE movie (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, duration_in_minutes INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE movie_format (id INT AUTO_INCREMENT NOT NULL, movie_id INT NOT NULL, format_id INT NOT NULL, INDEX IDX_277DEFAA8F93B6FC (movie_id), INDEX IDX_277DEFAAD629F605 (format_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, showtime_id INT NOT NULL, INDEX IDX_42C8495528BE1523 (showtime_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reservation_seat (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(15) NOT NULL, status_locked_expires_at DATETIME DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, showtime_id INT NOT NULL, seat_id INT NOT NULL, reservation_id INT DEFAULT NULL, INDEX IDX_2B65FB0E28BE1523 (showtime_id), INDEX IDX_2B65FB0EC1DAFE35 (seat_id), INDEX IDX_2B65FB0EB83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE screening_room (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(100) NOT NULL, status VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, maintenance_time_in_minutes INT DEFAULT NULL, cinema_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_56558C8D5E237E06 (name), UNIQUE INDEX UNIQ_56558C8D989D9B62 (slug), INDEX IDX_56558C8DB4CB84B6 (cinema_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE screening_room_seat (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(15) NOT NULL, type VARCHAR(15) NOT NULL, is_visible TINYINT(1) NOT NULL, screening_room_id INT NOT NULL, seat_id INT NOT NULL, INDEX IDX_8162D5439B3F89 (screening_room_id), INDEX IDX_8162D54C1DAFE35 (seat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE seat (id INT AUTO_INCREMENT NOT NULL, row_num INT NOT NULL, seat_num_in_row INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE showtime (id INT AUTO_INCREMENT NOT NULL, price INT NOT NULL, advertisement_time_in_minutes INT NOT NULL, slug VARCHAR(255) NOT NULL, is_published TINYINT(1) NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, screening_room_id INT NOT NULL, movie_format_id INT NOT NULL, cinema_id INT NOT NULL, INDEX IDX_3248D9139B3F89 (screening_room_id), INDEX IDX_3248D9145FCFF03 (movie_format_id), INDEX IDX_3248D91B4CB84B6 (cinema_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE cinema ADD CONSTRAINT FK_D48304B47E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE movie_format ADD CONSTRAINT FK_277DEFAA8F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE movie_format ADD CONSTRAINT FK_277DEFAAD629F605 FOREIGN KEY (format_id) REFERENCES format (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495528BE1523 FOREIGN KEY (showtime_id) REFERENCES showtime (id)');
        $this->addSql('ALTER TABLE reservation_seat ADD CONSTRAINT FK_2B65FB0E28BE1523 FOREIGN KEY (showtime_id) REFERENCES showtime (id)');
        $this->addSql('ALTER TABLE reservation_seat ADD CONSTRAINT FK_2B65FB0EC1DAFE35 FOREIGN KEY (seat_id) REFERENCES screening_room_seat (id)');
        $this->addSql('ALTER TABLE reservation_seat ADD CONSTRAINT FK_2B65FB0EB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE screening_room ADD CONSTRAINT FK_56558C8DB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
        $this->addSql('ALTER TABLE screening_room_seat ADD CONSTRAINT FK_8162D5439B3F89 FOREIGN KEY (screening_room_id) REFERENCES screening_room (id)');
        $this->addSql('ALTER TABLE screening_room_seat ADD CONSTRAINT FK_8162D54C1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id)');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D9139B3F89 FOREIGN KEY (screening_room_id) REFERENCES screening_room (id)');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D9145FCFF03 FOREIGN KEY (movie_format_id) REFERENCES movie_format (id)');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D91B4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cinema DROP FOREIGN KEY FK_D48304B47E3C61F9');
        $this->addSql('ALTER TABLE movie_format DROP FOREIGN KEY FK_277DEFAA8F93B6FC');
        $this->addSql('ALTER TABLE movie_format DROP FOREIGN KEY FK_277DEFAAD629F605');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495528BE1523');
        $this->addSql('ALTER TABLE reservation_seat DROP FOREIGN KEY FK_2B65FB0E28BE1523');
        $this->addSql('ALTER TABLE reservation_seat DROP FOREIGN KEY FK_2B65FB0EC1DAFE35');
        $this->addSql('ALTER TABLE reservation_seat DROP FOREIGN KEY FK_2B65FB0EB83297E7');
        $this->addSql('ALTER TABLE screening_room DROP FOREIGN KEY FK_56558C8DB4CB84B6');
        $this->addSql('ALTER TABLE screening_room_seat DROP FOREIGN KEY FK_8162D5439B3F89');
        $this->addSql('ALTER TABLE screening_room_seat DROP FOREIGN KEY FK_8162D54C1DAFE35');
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D9139B3F89');
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D9145FCFF03');
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D91B4CB84B6');
        $this->addSql('DROP TABLE cinema');
        $this->addSql('DROP TABLE format');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_format');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE reservation_seat');
        $this->addSql('DROP TABLE screening_room');
        $this->addSql('DROP TABLE screening_room_seat');
        $this->addSql('DROP TABLE seat');
        $this->addSql('DROP TABLE showtime');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
