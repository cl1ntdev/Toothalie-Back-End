<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206012355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY `FK_40374F40E5B533F9`');
        $this->addSql('ALTER TABLE reminder ADD viewed TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (appointment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F40E5B533F9');
        $this->addSql('ALTER TABLE reminder DROP viewed');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT `FK_40374F40E5B533F9` FOREIGN KEY (appointment_id) REFERENCES appointment (appointment_id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
