<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240920185146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cinema (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cinema_seat (cinema_id INT NOT NULL, seat_id INT NOT NULL, INDEX IDX_C7BDA3CB4CB84B6 (cinema_id), INDEX IDX_C7BDA3CC1DAFE35 (seat_id), PRIMARY KEY(cinema_id, seat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE screening_room (id INT AUTO_INCREMENT NOT NULL, cinema_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(100) NOT NULL, INDEX IDX_56558C8DB4CB84B6 (cinema_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE screening_room_seat (id INT AUTO_INCREMENT NOT NULL, screening_room_id INT NOT NULL, cinema_id INT DEFAULT NULL, seat_id INT DEFAULT NULL, seat_status VARCHAR(100) NOT NULL, seat_type VARCHAR(100) NOT NULL, INDEX IDX_8162D5439B3F89 (screening_room_id), INDEX IDX_8162D54B4CB84B6C1DAFE35 (cinema_id, seat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seat (id INT AUTO_INCREMENT NOT NULL, row_num VARCHAR(255) NOT NULL, col_num VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cinema_seat ADD CONSTRAINT FK_C7BDA3CB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
        $this->addSql('ALTER TABLE cinema_seat ADD CONSTRAINT FK_C7BDA3CC1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id)');
        $this->addSql('ALTER TABLE screening_room ADD CONSTRAINT FK_56558C8DB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
        $this->addSql('ALTER TABLE screening_room_seat ADD CONSTRAINT FK_8162D5439B3F89 FOREIGN KEY (screening_room_id) REFERENCES screening_room (id)');
        $this->addSql('ALTER TABLE screening_room_seat ADD CONSTRAINT FK_8162D54B4CB84B6C1DAFE35 FOREIGN KEY (cinema_id, seat_id) REFERENCES cinema_seat (cinema_id, seat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cinema_seat DROP FOREIGN KEY FK_C7BDA3CB4CB84B6');
        $this->addSql('ALTER TABLE cinema_seat DROP FOREIGN KEY FK_C7BDA3CC1DAFE35');
        $this->addSql('ALTER TABLE screening_room DROP FOREIGN KEY FK_56558C8DB4CB84B6');
        $this->addSql('ALTER TABLE screening_room_seat DROP FOREIGN KEY FK_8162D5439B3F89');
        $this->addSql('ALTER TABLE screening_room_seat DROP FOREIGN KEY FK_8162D54B4CB84B6C1DAFE35');
        $this->addSql('DROP TABLE cinema');
        $this->addSql('DROP TABLE cinema_seat');
        $this->addSql('DROP TABLE screening_room');
        $this->addSql('DROP TABLE screening_room_seat');
        $this->addSql('DROP TABLE seat');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
