<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306012029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promo_code (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(64) NOT NULL, discount_percent INT NOT NULL, email VARCHAR(180) DEFAULT NULL, is_used TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_3D8C939E77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D9171F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE commande ADD email VARCHAR(180) DEFAULT NULL, ADD telephone VARCHAR(30) DEFAULT NULL, ADD shipping_address VARCHAR(255) DEFAULT NULL, ADD shipping_city VARCHAR(100) DEFAULT NULL, ADD shipping_postal_code VARCHAR(20) DEFAULT NULL, ADD shipping_country VARCHAR(2) DEFAULT NULL, ADD shipping_cost DOUBLE PRECISION DEFAULT NULL, ADD shipping_carrier VARCHAR(60) DEFAULT NULL, ADD shipping_eta_days INT DEFAULT NULL, ADD shipping_tracking VARCHAR(100) DEFAULT NULL, ADD payment_status VARCHAR(30) DEFAULT NULL, ADD paid_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE event_requirement ADD CONSTRAINT FK_70B686D07B576F77 FOREIGN KEY (requirement_id) REFERENCES requirement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit ADD categorie VARCHAR(50) DEFAULT NULL, ADD image_name VARCHAR(255) DEFAULT NULL, ADD poids_kg DOUBLE PRECISION DEFAULT NULL, ADD sku VARCHAR(64) DEFAULT NULL, ADD rating_average DOUBLE PRECISION DEFAULT NULL, ADD rating_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE requirement CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE promo_code');
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D9171F7E88B');
        $this->addSql('ALTER TABLE commande DROP email, DROP telephone, DROP shipping_address, DROP shipping_city, DROP shipping_postal_code, DROP shipping_country, DROP shipping_cost, DROP shipping_carrier, DROP shipping_eta_days, DROP shipping_tracking, DROP payment_status, DROP paid_at');
        $this->addSql('ALTER TABLE event_requirement DROP FOREIGN KEY FK_70B686D07B576F77');
        $this->addSql('ALTER TABLE produit DROP categorie, DROP image_name, DROP poids_kg, DROP sku, DROP rating_average, DROP rating_count');
        $this->addSql('ALTER TABLE requirement MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE requirement CHANGE id id INT NOT NULL, DROP PRIMARY KEY');
    }
}
