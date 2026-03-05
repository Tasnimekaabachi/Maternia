<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260221100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne categorie à la table produit pour le filtrage marketplace.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit ADD categorie VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit DROP categorie');
    }
}
