<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115063405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dentist (dentistID INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, experience VARCHAR(50) NOT NULL, specialty VARCHAR(100) NOT NULL, username VARCHAR(100) NOT NULL, password VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_6C8FB839E7927C74 (email), PRIMARY KEY (dentistID)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE dentist_service (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_AFE90E7FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE doctor_service (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patient (patient_id INT AUTO_INCREMENT NOT NULL, username VARCHAR(100) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, role VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, contact_no VARCHAR(15) NOT NULL, email VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_1ADAD7EBF85E0677 (username), PRIMARY KEY (patient_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, service_type_id INT NOT NULL, UNIQUE INDEX UNIQ_E19D9AD2AC8DE0F (service_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE dentist_service ADD CONSTRAINT FK_AFE90E7FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2AC8DE0F FOREIGN KEY (service_type_id) REFERENCES service_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dentist_service DROP FOREIGN KEY FK_AFE90E7FA76ED395');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2AC8DE0F');
        $this->addSql('DROP TABLE dentist');
        $this->addSql('DROP TABLE dentist_service');
        $this->addSql('DROP TABLE doctor_service');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE service_type');
    }
}
