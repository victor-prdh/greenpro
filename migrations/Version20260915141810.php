<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260915141810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (company_name VARCHAR(255) NOT NULL, contact_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(150) NOT NULL, uuid BINARY(16) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_81398E09E7927C74 (email), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE location (start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, total_price NUMERIC(10, 2) NOT NULL, uuid BINARY(16) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, customer_id BINARY(16) DEFAULT NULL, INDEX IDX_5E9E89CB9395C3F3 (customer_id), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE location_material (location_uuid BINARY(16) NOT NULL, material_uuid BINARY(16) NOT NULL, INDEX IDX_ADEB5777517BE5E6 (location_uuid), INDEX IDX_ADEB577785173D93 (material_uuid), PRIMARY KEY (location_uuid, material_uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE material (name VARCHAR(255) NOT NULL, reference VARCHAR(255) NOT NULL, daily_price NUMERIC(10, 2) NOT NULL, status VARCHAR(255) NOT NULL, uuid BINARY(16) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_7CBE7595AEA34913 (reference), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, uuid BINARY(16) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (uuid)');
        $this->addSql('ALTER TABLE location_material ADD CONSTRAINT FK_ADEB5777517BE5E6 FOREIGN KEY (location_uuid) REFERENCES location (uuid)');
        $this->addSql('ALTER TABLE location_material ADD CONSTRAINT FK_ADEB577785173D93 FOREIGN KEY (material_uuid) REFERENCES material (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB9395C3F3');
        $this->addSql('ALTER TABLE location_material DROP FOREIGN KEY FK_ADEB5777517BE5E6');
        $this->addSql('ALTER TABLE location_material DROP FOREIGN KEY FK_ADEB577785173D93');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE location_material');
        $this->addSql('DROP TABLE material');
        $this->addSql('DROP TABLE user');
    }
}
