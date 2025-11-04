<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104102033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment_log DROP patient_id, DROP dentist_id, CHANGE appointment_id appointment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment_log ADD CONSTRAINT FK_206FFFDDE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (appointment_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_206FFFDDE5B533F9 ON appointment_log (appointment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment_log DROP FOREIGN KEY FK_206FFFDDE5B533F9');
        $this->addSql('DROP INDEX IDX_206FFFDDE5B533F9 ON appointment_log');
        $this->addSql('ALTER TABLE appointment_log ADD patient_id INT DEFAULT NULL, ADD dentist_id INT DEFAULT NULL, CHANGE appointment_id appointment_id INT NOT NULL');
    }
}
