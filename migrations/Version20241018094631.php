<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241018094631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cinema ADD max_rows INT NOT NULL, ADD max_seats_per_row INT NOT NULL, DROP rows_max, DROP seats_per_row_max');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cinema ADD rows_max INT NOT NULL, ADD seats_per_row_max INT NOT NULL, DROP max_rows, DROP max_seats_per_row');
    }
}
