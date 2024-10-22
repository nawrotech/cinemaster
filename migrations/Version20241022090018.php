<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022090018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screening_setup_type DROP FOREIGN KEY FK_E0EF953EF4D381DB');
        $this->addSql('DROP INDEX IDX_E0EF953EF4D381DB ON screening_setup_type');
        $this->addSql('ALTER TABLE screening_setup_type DROP visual_format_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screening_setup_type ADD visual_format_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE screening_setup_type ADD CONSTRAINT FK_E0EF953EF4D381DB FOREIGN KEY (visual_format_id) REFERENCES visual_format (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E0EF953EF4D381DB ON screening_setup_type (visual_format_id)');
    }
}
