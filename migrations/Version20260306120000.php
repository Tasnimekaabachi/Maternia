<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix schema: FK rename on message, nullable email on offre_baby_sitter, DC2Type comments, messenger_messages.';
    }

    public function up(Schema $schema): void
    {
        // email nullable + FK rename already applied (partial run); only fix what's left

        // Fix index name on message.conversation_id (MariaDB 10.4 compatible — no RENAME INDEX)
        $this->addSql("ALTER TABLE message DROP INDEX IDX_B6BD307F1AC55BF, ADD INDEX IDX_B6BD307F9AC0396 (conversation_id)");

        // Remove DC2Type:datetime_immutable comments so doctrine:schema:validate stays clean
        $this->addSql("ALTER TABLE conversation CHANGE created_at created_at DATETIME NOT NULL");
        $this->addSql("ALTER TABLE message CHANGE created_at created_at DATETIME NOT NULL");
        $this->addSql("ALTER TABLE reservation CHANGE created_at created_at DATETIME NOT NULL");

        // Fix messenger_messages.delivered_at nullability
        $this->addSql("ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE message DROP INDEX IDX_B6BD307F9AC0396, ADD INDEX IDX_B6BD307F1AC55BF (conversation_id)");
        $this->addSql("ALTER TABLE offre_baby_sitter CHANGE email email VARCHAR(255) NOT NULL");
    }
}
