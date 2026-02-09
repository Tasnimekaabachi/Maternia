<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207145136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE maman (id INT AUTO_INCREMENT NOT NULL, numero_urgence VARCHAR(30) NOT NULL, groupe_sanguin VARCHAR(20) NOT NULL, allergies LONGTEXT DEFAULT NULL, antecedents_medicaux LONGTEXT DEFAULT NULL, poids DOUBLE PRECISION DEFAULT NULL, taille DOUBLE PRECISION NOT NULL, maladies_chroniques LONGTEXT DEFAULT NULL, medicaments_actuels LONGTEXT DEFAULT NULL, fumeur TINYINT(1) NOT NULL, consommation_alcool TINYINT(1) NOT NULL, niveau_activite_physique VARCHAR(50) NOT NULL, habitudes_alimentaires VARCHAR(100) NOT NULL, date_creation DATETIME NOT NULL, date_mise_ajour DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE maman');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
