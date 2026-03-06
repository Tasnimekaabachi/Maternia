<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove ON DELETE CASCADE from FKs (Doctrine manages cascades at app level); fix delivered_at.';
    }

    public function up(Schema $schema): void
    {
        // conversation.offre_id FK — remove ON DELETE CASCADE (Doctrine handles cascade at app level)
        $this->addSql("ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E94CC8505A");
        $this->addSql("ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E94CC8505A FOREIGN KEY (offre_id) REFERENCES offre_baby_sitter (id)");

        // message.conversation_id FK — remove ON DELETE CASCADE
        $this->addSql("ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396");
        $this->addSql("ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)");

        // reservation.offre_id FK — remove ON DELETE CASCADE
        $this->addSql("ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554CC8505A");
        $this->addSql("ALTER TABLE reservation ADD CONSTRAINT FK_42C849554CC8505A FOREIGN KEY (offre_id) REFERENCES offre_baby_sitter (id)");

        // Fix messenger_messages.delivered_at nullability
        $this->addSql("ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E94CC8505A");
        $this->addSql("ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E94CC8505A FOREIGN KEY (offre_id) REFERENCES offre_baby_sitter (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396");
        $this->addSql("ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554CC8505A");
        $this->addSql("ALTER TABLE reservation ADD CONSTRAINT FK_42C849554CC8505A FOREIGN KEY (offre_id) REFERENCES offre_baby_sitter (id) ON DELETE CASCADE");
    }
}
