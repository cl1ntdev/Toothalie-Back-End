<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115065131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dentist_service_service DROP FOREIGN KEY `FK_73A6C1C37EB99DA0`');
        $this->addSql('ALTER TABLE dentist_service_service DROP FOREIGN KEY `FK_73A6C1C3ED5CA9E6`');
        $this->addSql('DROP TABLE dentist_service_service');
        $this->addSql('ALTER TABLE dentist_service ADD service_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE dentist_service ADD CONSTRAINT FK_AFE90E7FED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('CREATE INDEX IDX_AFE90E7FED5CA9E6 ON dentist_service (service_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dentist_service_service (dentist_service_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_73A6C1C37EB99DA0 (dentist_service_id), INDEX IDX_73A6C1C3ED5CA9E6 (service_id), PRIMARY KEY (dentist_service_id, service_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE dentist_service_service ADD CONSTRAINT `FK_73A6C1C37EB99DA0` FOREIGN KEY (dentist_service_id) REFERENCES dentist_service (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dentist_service_service ADD CONSTRAINT `FK_73A6C1C3ED5CA9E6` FOREIGN KEY (service_id) REFERENCES service (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dentist_service DROP FOREIGN KEY FK_AFE90E7FED5CA9E6');
        $this->addSql('DROP INDEX IDX_AFE90E7FED5CA9E6 ON dentist_service');
        $this->addSql('ALTER TABLE dentist_service DROP service_id, CHANGE user_id user_id INT DEFAULT NULL');
    }
}
