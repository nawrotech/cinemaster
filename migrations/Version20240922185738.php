<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240922185738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cinema_history (id INT AUTO_INCREMENT NOT NULL, changed_at DATETIME NOT NULL, changes JSON NOT NULL, cinema_id INT NOT NULL, INDEX IDX_1150D297B4CB84B6 (cinema_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE cinema_history ADD CONSTRAINT FK_1150D297B4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id)');
        $this->addSql('ALTER TABLE seat CHANGE row_num row_num INT NOT NULL, CHANGE col_num col_num INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cinema_history DROP FOREIGN KEY FK_1150D297B4CB84B6');
        $this->addSql('DROP TABLE cinema_history');
        $this->addSql('ALTER TABLE seat CHANGE row_num row_num VARCHAR(255) NOT NULL, CHANGE col_num col_num VARCHAR(255) NOT NULL');
    }
}
