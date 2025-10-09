<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251009021629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD appointment_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844546FBEBB FOREIGN KEY (appointment_type_id) REFERENCES appointment_type (id)');
        $this->addSql('CREATE INDEX IDX_FE38F844546FBEBB ON appointment (appointment_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844546FBEBB');
        $this->addSql('DROP INDEX IDX_FE38F844546FBEBB ON appointment');
        $this->addSql('ALTER TABLE appointment DROP appointment_type_id');
    }
}
