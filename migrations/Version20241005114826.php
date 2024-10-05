<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241005114826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D918F93B6FC');
        $this->addSql('DROP INDEX IDX_3248D918F93B6FC ON showtime');
        $this->addSql('ALTER TABLE showtime CHANGE movie_id movie_format_id INT NOT NULL');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D9145FCFF03 FOREIGN KEY (movie_format_id) REFERENCES movie_movie_type (id)');
        $this->addSql('CREATE INDEX IDX_3248D9145FCFF03 ON showtime (movie_format_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D9145FCFF03');
        $this->addSql('DROP INDEX IDX_3248D9145FCFF03 ON showtime');
        $this->addSql('ALTER TABLE showtime CHANGE movie_format_id movie_id INT NOT NULL');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D918F93B6FC FOREIGN KEY (movie_id) REFERENCES movie_movie_type (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3248D918F93B6FC ON showtime (movie_id)');
    }
}
