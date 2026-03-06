<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute email (nullable) à offre_baby_sitter pour envoi alertes par mail.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE offre_baby_sitter ADD email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE offre_baby_sitter DROP email');
    }
}
