<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260616223230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create oauth_clients table with FK to users (CASCADE)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE oauth_clients (id UUID NOT NULL, owner_id UUID NOT NULL, name VARCHAR(255) NOT NULL, redirect_uris TEXT[] NOT NULL, grant_types TEXT[] NOT NULL, is_confidential BOOLEAN NOT NULL, client_secret_hash_value VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_oauth_clients_owner ON oauth_clients (owner_id)');
        $this->addSql('ALTER TABLE oauth_clients ADD CONSTRAINT FK_oauth_clients_owner FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_clients DROP CONSTRAINT FK_oauth_clients_owner');
        $this->addSql('DROP TABLE oauth_clients');
    }
}
