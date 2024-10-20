<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020113709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE showtime_visual_format (showtime_id INT NOT NULL, visual_format_id INT NOT NULL, INDEX IDX_138A126428BE1523 (showtime_id), INDEX IDX_138A1264F4D381DB (visual_format_id), PRIMARY KEY(showtime_id, visual_format_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE showtime_visual_format ADD CONSTRAINT FK_138A126428BE1523 FOREIGN KEY (showtime_id) REFERENCES showtime (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE showtime_visual_format ADD CONSTRAINT FK_138A1264F4D381DB FOREIGN KEY (visual_format_id) REFERENCES visual_format (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE showtime_visual_format DROP FOREIGN KEY FK_138A126428BE1523');
        $this->addSql('ALTER TABLE showtime_visual_format DROP FOREIGN KEY FK_138A1264F4D381DB');
        $this->addSql('DROP TABLE showtime_visual_format');
    }
}
