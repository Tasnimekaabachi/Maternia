<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260221220058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grosesse ADD nausee TINYINT(1) DEFAULT 0 NOT NULL, ADD vomissement TINYINT(1) DEFAULT 0 NOT NULL, ADD saignement TINYINT(1) DEFAULT 0 NOT NULL, ADD fievre TINYINT(1) DEFAULT 0 NOT NULL, ADD douleur_abdominale TINYINT(1) DEFAULT 0 NOT NULL, ADD fatigue TINYINT(1) DEFAULT 0 NOT NULL, ADD vertiges TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grosesse DROP nausee, DROP vomissement, DROP saignement, DROP fievre, DROP douleur_abdominale, DROP fatigue, DROP vertiges');
    }
}
