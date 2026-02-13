<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209141653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_baby_sitter (id INT AUTO_INCREMENT NOT NULL, nom_parent VARCHAR(255) NOT NULL, email_parent VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, date_demande DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, offre_id INT NOT NULL, INDEX IDX_E09A060E4CC8505A (offre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE offre_baby_sitter (id INT AUTO_INCREMENT NOT NULL, nom_babysitter VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, experience INT NOT NULL, ville VARCHAR(100) NOT NULL, tarif DOUBLE PRECISION NOT NULL, description LONGTEXT NOT NULL, disponibilite TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE demande_baby_sitter ADD CONSTRAINT FK_E09A060E4CC8505A FOREIGN KEY (offre_id) REFERENCES offre_baby_sitter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_baby_sitter DROP FOREIGN KEY FK_E09A060E4CC8505A');
        $this->addSql('DROP TABLE demande_baby_sitter');
        $this->addSql('DROP TABLE offre_baby_sitter');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
