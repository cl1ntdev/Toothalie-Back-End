<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110075806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY `FK_5A3811FBDAEDB9B1`');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBDAEDB9B1 FOREIGN KEY (dentistID) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBDAEDB9B1');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT `FK_5A3811FBDAEDB9B1` FOREIGN KEY (dentistID) REFERENCES dentist (dentistID) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
