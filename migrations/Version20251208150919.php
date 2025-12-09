<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208150919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, username VARCHAR(100) NOT NULL, role VARCHAR(50) NOT NULL, action VARCHAR(50) NOT NULL, target_data LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (appointment_id)');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activity_log');
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F40E5B533F9');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
}
