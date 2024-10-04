<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003152223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE movie (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, duration_in_minutes INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE movie_movie_type (movie_id INT NOT NULL, movie_type_id INT NOT NULL, INDEX IDX_964806188F93B6FC (movie_id), INDEX IDX_96480618E5DACEBD (movie_type_id), PRIMARY KEY(movie_id, movie_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE movie_type (id INT AUTO_INCREMENT NOT NULL, audio_version VARCHAR(255) NOT NULL, visual_version VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE showtime (id INT AUTO_INCREMENT NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, price INT NOT NULL, screening_room_id INT NOT NULL, movie_id INT NOT NULL, INDEX IDX_3248D9139B3F89 (screening_room_id), INDEX IDX_3248D918F93B6FC (movie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE movie_movie_type ADD CONSTRAINT FK_964806188F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_movie_type ADD CONSTRAINT FK_96480618E5DACEBD FOREIGN KEY (movie_type_id) REFERENCES movie_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D9139B3F89 FOREIGN KEY (screening_room_id) REFERENCES screening_room (id)');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D918F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie_movie_type DROP FOREIGN KEY FK_964806188F93B6FC');
        $this->addSql('ALTER TABLE movie_movie_type DROP FOREIGN KEY FK_96480618E5DACEBD');
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D9139B3F89');
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D918F93B6FC');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_movie_type');
        $this->addSql('DROP TABLE movie_type');
        $this->addSql('DROP TABLE showtime');
    }
}
