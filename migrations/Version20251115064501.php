<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115064501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE doctor_service');
        $this->addSql('ALTER TABLE service DROP INDEX UNIQ_E19D9AD2AC8DE0F, ADD INDEX IDX_E19D9AD2AC8DE0F (service_type_id)');
        $this->addSql('ALTER TABLE service_type CHANGE name name VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doctor_service (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE service DROP INDEX IDX_E19D9AD2AC8DE0F, ADD UNIQUE INDEX UNIQ_E19D9AD2AC8DE0F (service_type_id)');
        $this->addSql('ALTER TABLE service_type CHANGE name name VARCHAR(50) DEFAULT NULL');
    }
}
