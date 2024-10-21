<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021143322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE screening_setup_type (id INT AUTO_INCREMENT NOT NULL, sound_format VARCHAR(50) NOT NULL, visual_format_id INT DEFAULT NULL, cinema_id INT NOT NULL, INDEX IDX_E0EF953EF4D381DB (visual_format_id), INDEX IDX_E0EF953EB4CB84B6 (cinema_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE screening_setup_type ADD CONSTRAINT FK_E0EF953EF4D381DB FOREIGN KEY (visual_format_id) REFERENCES visual_format (id)');
        $this->addSql('ALTER TABLE screening_setup_type ADD CONSTRAINT FK_E0EF953EB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
        $this->addSql('ALTER TABLE screening_room_type DROP FOREIGN KEY FK_B9944C1BF4D381DB');
        $this->addSql('DROP TABLE screening_room_type');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE screening_room_type (id INT AUTO_INCREMENT NOT NULL, sound_format VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, visual_format_id INT NOT NULL, INDEX IDX_B9944C1BF4D381DB (visual_format_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE screening_room_type ADD CONSTRAINT FK_B9944C1BF4D381DB FOREIGN KEY (visual_format_id) REFERENCES visual_format (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE screening_setup_type DROP FOREIGN KEY FK_E0EF953EF4D381DB');
        $this->addSql('ALTER TABLE screening_setup_type DROP FOREIGN KEY FK_E0EF953EB4CB84B6');
        $this->addSql('DROP TABLE screening_setup_type');
    }
}
