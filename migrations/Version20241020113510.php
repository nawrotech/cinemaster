<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020113510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audio_format (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE visual_format (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE format ADD visual_format_id INT NOT NULL, ADD audio_format_id INT NOT NULL, DROP audio_version, DROP visual_version');
        $this->addSql('ALTER TABLE format ADD CONSTRAINT FK_DEBA72DFF4D381DB FOREIGN KEY (visual_format_id) REFERENCES visual_format (id)');
        $this->addSql('ALTER TABLE format ADD CONSTRAINT FK_DEBA72DF8278FB05 FOREIGN KEY (audio_format_id) REFERENCES audio_format (id)');
        $this->addSql('CREATE INDEX IDX_DEBA72DFF4D381DB ON format (visual_format_id)');
        $this->addSql('CREATE INDEX IDX_DEBA72DF8278FB05 ON format (audio_format_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE audio_format');
        $this->addSql('DROP TABLE visual_format');
        $this->addSql('ALTER TABLE format DROP FOREIGN KEY FK_DEBA72DFF4D381DB');
        $this->addSql('ALTER TABLE format DROP FOREIGN KEY FK_DEBA72DF8278FB05');
        $this->addSql('DROP INDEX IDX_DEBA72DFF4D381DB ON format');
        $this->addSql('DROP INDEX IDX_DEBA72DF8278FB05 ON format');
        $this->addSql('ALTER TABLE format ADD audio_version VARCHAR(255) NOT NULL, ADD visual_version VARCHAR(255) NOT NULL, DROP visual_format_id, DROP audio_format_id');
    }
}
