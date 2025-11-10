<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110075628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY `FK_FE38F8441CE0A142`');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY `FK_FE38F8446B899279`');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8441CE0A142 FOREIGN KEY (dentist_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8446B899279 FOREIGN KEY (patient_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8446B899279');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8441CE0A142');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT `FK_FE38F8446B899279` FOREIGN KEY (patient_id) REFERENCES patient (patient_id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT `FK_FE38F8441CE0A142` FOREIGN KEY (dentist_id) REFERENCES dentist (dentistID) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
