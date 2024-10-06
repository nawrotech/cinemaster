<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241006091557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE showtime ADD published TINYINT(1) NOT NULL, ADD cinema_id INT NOT NULL');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D91B4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
        $this->addSql('CREATE INDEX IDX_3248D91B4CB84B6 ON showtime (cinema_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D91B4CB84B6');
        $this->addSql('DROP INDEX IDX_3248D91B4CB84B6 ON showtime');
        $this->addSql('ALTER TABLE showtime DROP published, DROP cinema_id');
    }
}
