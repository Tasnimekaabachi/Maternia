<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Messagerie (conversation, message) et réservations (reservation).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE conversation (
            id INT AUTO_INCREMENT NOT NULL,
            offre_id INT NOT NULL,
            parent_email VARCHAR(255) NOT NULL,
            parent_name VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_8A8E26E94CC8505A (offre_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_8A8E26E94CC8505A FOREIGN KEY (offre_id) REFERENCES offre_baby_sitter (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (
            id INT AUTO_INCREMENT NOT NULL,
            conversation_id INT NOT NULL,
            contenu LONGTEXT NOT NULL,
            envoye_par VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_B6BD307F1AC55BF (conversation_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_B6BD307F1AC55BF FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (
            id INT AUTO_INCREMENT NOT NULL,
            offre_id INT NOT NULL,
            parent_email VARCHAR(255) NOT NULL,
            parent_name VARCHAR(255) NOT NULL,
            date_debut DATETIME NOT NULL,
            date_fin DATETIME NOT NULL,
            statut VARCHAR(20) NOT NULL,
            message LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_42C849554CC8505A (offre_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_42C849554CC8505A FOREIGN KEY (offre_id) REFERENCES offre_baby_sitter (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE conversation');
    }
}
