<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260621120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create authorization_codes table with FK to oauth_clients and users (CASCADE)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE authorization_codes (
            id UUID NOT NULL,
            client_id UUID NOT NULL,
            user_id UUID NOT NULL,
            redirect_uri VARCHAR(255) NOT NULL,
            scopes TEXT[] NOT NULL,
            code VARCHAR(255) NOT NULL,
            code_challenge VARCHAR(255) NOT NULL,
            code_challenge_method VARCHAR(255) NOT NULL,
            state VARCHAR(255) DEFAULT NULL,
            expired_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
            used BOOLEAN NOT NULL,
            PRIMARY KEY (id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_authcodes_code ON authorization_codes (code)');
        $this->addSql('CREATE INDEX IDX_authcodes_client ON authorization_codes (client_id)');
        $this->addSql('CREATE INDEX IDX_authcodes_user ON authorization_codes (user_id)');
        $this->addSql('ALTER TABLE authorization_codes ADD CONSTRAINT FK_authcodes_client FOREIGN KEY (client_id) REFERENCES oauth_clients (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE authorization_codes ADD CONSTRAINT FK_authcodes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE authorization_codes DROP CONSTRAINT FK_authcodes_client');
        $this->addSql('ALTER TABLE authorization_codes DROP CONSTRAINT FK_authcodes_user');
        $this->addSql('DROP TABLE authorization_codes');
    }
}
