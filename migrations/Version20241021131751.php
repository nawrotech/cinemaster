<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021131751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE format DROP FOREIGN KEY FK_DEBA72DF8278FB05');
        $this->addSql('DROP INDEX IDX_DEBA72DF8278FB05 ON format');
        $this->addSql('ALTER TABLE format ADD language_presentation VARCHAR(15) NOT NULL, DROP audio_format_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE format ADD audio_format_id INT NOT NULL, DROP language_presentation');
        $this->addSql('ALTER TABLE format ADD CONSTRAINT FK_DEBA72DF8278FB05 FOREIGN KEY (audio_format_id) REFERENCES audio_format (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_DEBA72DF8278FB05 ON format (audio_format_id)');
    }
}
