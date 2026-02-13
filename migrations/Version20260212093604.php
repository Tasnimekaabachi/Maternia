<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212093604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, categorie VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, pour VARCHAR(50) NOT NULL, image VARCHAR(255) DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, statut TINYINT(1) NOT NULL, ordre_affichage INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consultation_creneau (id INT AUTO_INCREMENT NOT NULL, nom_medecin VARCHAR(100) NOT NULL, photo_medecin VARCHAR(255) DEFAULT NULL, description_medecin LONGTEXT DEFAULT NULL, specialite_medecin VARCHAR(100) DEFAULT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, jour DATE DEFAULT NULL, heure_debut TIME DEFAULT NULL, heure_fin TIME DEFAULT NULL, statut_reservation VARCHAR(20) NOT NULL, duree_minutes INT DEFAULT NULL, nombre_places INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, consultation_id INT NOT NULL, INDEX IDX_BC657ABD62FF6CDF (consultation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservation_client (id INT AUTO_INCREMENT NOT NULL, nom_client VARCHAR(100) NOT NULL, prenom_client VARCHAR(100) NOT NULL, email_client VARCHAR(100) NOT NULL, telephone_client VARCHAR(20) NOT NULL, type_patient VARCHAR(20) NOT NULL, mois_grossesse INT DEFAULT NULL, date_naissance_bebe DATE DEFAULT NULL, statut_reservation VARCHAR(50) NOT NULL, reference VARCHAR(50) NOT NULL, date_reservation DATETIME NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, consultation_creneau_id INT NOT NULL, UNIQUE INDEX UNIQ_8FB54DCE43CFAC64 (consultation_creneau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE consultation_creneau ADD CONSTRAINT FK_BC657ABD62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE reservation_client ADD CONSTRAINT FK_8FB54DCE43CFAC64 FOREIGN KEY (consultation_creneau_id) REFERENCES consultation_creneau (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation_creneau DROP FOREIGN KEY FK_BC657ABD62FF6CDF');
        $this->addSql('ALTER TABLE reservation_client DROP FOREIGN KEY FK_8FB54DCE43CFAC64');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE consultation_creneau');
        $this->addSql('DROP TABLE reservation_client');
    }
}
