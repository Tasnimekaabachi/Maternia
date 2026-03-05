<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute poids/SKU produit et champs livraison/paiement sur commande.';
    }

    public function up(Schema $schema): void
    {
        // Produit
        $this->addSql('ALTER TABLE produit ADD poids_kg DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD sku VARCHAR(64) DEFAULT NULL');

        // Commande
        $this->addSql('ALTER TABLE commande ADD email VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD telephone VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD shipping_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD shipping_city VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD shipping_postal_code VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD shipping_country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD shipping_cost DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD shipping_carrier VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD shipping_eta_days INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD shipping_tracking VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD payment_status VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD paid_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Commande
        $this->addSql('ALTER TABLE commande DROP email');
        $this->addSql('ALTER TABLE commande DROP telephone');
        $this->addSql('ALTER TABLE commande DROP shipping_address');
        $this->addSql('ALTER TABLE commande DROP shipping_city');
        $this->addSql('ALTER TABLE commande DROP shipping_postal_code');
        $this->addSql('ALTER TABLE commande DROP shipping_country');
        $this->addSql('ALTER TABLE commande DROP shipping_cost');
        $this->addSql('ALTER TABLE commande DROP shipping_carrier');
        $this->addSql('ALTER TABLE commande DROP shipping_eta_days');
        $this->addSql('ALTER TABLE commande DROP shipping_tracking');
        $this->addSql('ALTER TABLE commande DROP payment_status');
        $this->addSql('ALTER TABLE commande DROP paid_at');

        // Produit
        $this->addSql('ALTER TABLE produit DROP poids_kg');
        $this->addSql('ALTER TABLE produit DROP sku');
    }
}

