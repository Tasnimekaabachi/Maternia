<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226191500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes ratingAverage et ratingCount sur produit pour la notation utilisateur.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit ADD rating_average DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD rating_count INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit DROP rating_average');
        $this->addSql('ALTER TABLE produit DROP rating_count');
    }
}

