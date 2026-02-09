<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Poids obligatoire : plage 30–140 kg. Met à jour les NULL existants puis rend la colonne NOT NULL.
 */
final class Version20260208120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rendre poids non null (30–140 kg) sur maman';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE maman SET poids = 60 WHERE poids IS NULL');
        $this->addSql('ALTER TABLE maman CHANGE poids poids DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE maman CHANGE poids poids DOUBLE PRECISION DEFAULT NULL');
    }
}
