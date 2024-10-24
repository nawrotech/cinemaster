<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024161150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie_screening_format ADD cinema_id INT NOT NULL');
        $this->addSql('ALTER TABLE movie_screening_format ADD CONSTRAINT FK_907F973AB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
        $this->addSql('CREATE INDEX IDX_907F973AB4CB84B6 ON movie_screening_format (cinema_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie_screening_format DROP FOREIGN KEY FK_907F973AB4CB84B6');
        $this->addSql('DROP INDEX IDX_907F973AB4CB84B6 ON movie_screening_format');
        $this->addSql('ALTER TABLE movie_screening_format DROP cinema_id');
    }
}
