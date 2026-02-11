<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208184607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, categorie VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, pour VARCHAR(50) NOT NULL, image VARCHAR(255) DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, statut TINYINT(1) NOT NULL, ordre_affichage INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consultation_creneau (id INT AUTO_INCREMENT NOT NULL, nom_medecin VARCHAR(100) NOT NULL, photo_medecin VARCHAR(255) DEFAULT NULL, description_medecin LONGTEXT DEFAULT NULL, specialite_medecin VARCHAR(100) DEFAULT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, disponible TINYINT(1) NOT NULL, nom_client VARCHAR(100) DEFAULT NULL, prenom_client VARCHAR(100) DEFAULT NULL, email_client VARCHAR(100) DEFAULT NULL, telephone_client VARCHAR(20) DEFAULT NULL, type_patient VARCHAR(20) DEFAULT NULL, mois_grossesse INT DEFAULT NULL, date_naissance_bebe DATE DEFAULT NULL, urgence TINYINT(1) DEFAULT NULL, statut_reservation VARCHAR(20) NOT NULL, reference VARCHAR(50) DEFAULT NULL, date_reservation DATETIME DEFAULT NULL, avis_client LONGTEXT DEFAULT NULL, rating_client INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, consultation_id INT NOT NULL, INDEX IDX_BC657ABD62FF6CDF (consultation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE consultation_creneau ADD CONSTRAINT FK_BC657ABD62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation_creneau DROP FOREIGN KEY FK_BC657ABD62FF6CDF');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE consultation_creneau');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
