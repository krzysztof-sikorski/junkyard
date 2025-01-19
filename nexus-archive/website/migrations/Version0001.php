<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user, user_access_token, and nexus_raw_data tables';
    }

    public function up(Schema $schema): void
    {
        // create user table
        $this->addSql(
            <<<'SQL'
            CREATE TABLE "user" (
                id UUID NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                username VARCHAR(180) NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                enabled BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )
            SQL
        );
        $this->addSql('CREATE UNIQUE INDEX username_uniq ON "user" (username)');
        $this->addSql("COMMENT ON COLUMN \"user\".id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN \"user\".created_at IS '(DC2Type:datetimetz_immutable)'");

        // create user_access_token table
        $this->addSql(
            <<<'SQL'
            CREATE TABLE user_access_token (
                id UUID NOT NULL,
                owner_id UUID NOT NULL,
                value TEXT NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                valid_until TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
            SQL
        );
        $this->addSql('CREATE INDEX IDX_366EA16A7E3C61F9 ON user_access_token (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX value_uniq ON user_access_token (value)');
        $this->addSql("COMMENT ON COLUMN user_access_token.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN user_access_token.owner_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN user_access_token.created_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql("COMMENT ON COLUMN user_access_token.valid_until IS '(DC2Type:datetimetz_immutable)'");

        // create nexus_raw_data table
        $this->addSql(
            <<<'SQL'
            CREATE TABLE nexus_raw_data (
                id UUID NOT NULL,
                submitter_id UUID NOT NULL,
                submitted_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                request_started_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                response_completed_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                method TEXT NOT NULL,
                url TEXT NOT NULL,
                form_data JSON DEFAULT NULL,
                response_body TEXT NOT NULL,
                PRIMARY KEY(id)
            )
            SQL
        );
        $this->addSql(
            'CREATE INDEX nexus_raw_data_sorting_idx ON nexus_raw_data (submitted_at, request_started_at, id)'
        );
        $this->addSql('CREATE INDEX nexus_raw_data_submitter_idx ON nexus_raw_data (submitter_id)');
        $this->addSql("COMMENT ON COLUMN nexus_raw_data.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN nexus_raw_data.submitter_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN nexus_raw_data.submitted_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql("COMMENT ON COLUMN nexus_raw_data.request_started_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql("COMMENT ON COLUMN nexus_raw_data.response_completed_at IS '(DC2Type:datetimetz_immutable)'");

        // create foreign keys
        $this->addSql(
            <<<'SQL'
            ALTER TABLE nexus_raw_data ADD CONSTRAINT FK_7BE0EB04919E5513
            FOREIGN KEY (submitter_id) REFERENCES "user" (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL
        );
        $this->addSql(
            <<<'SQL'
            ALTER TABLE user_access_token ADD CONSTRAINT FK_366EA16A7E3C61F9
            FOREIGN KEY (owner_id) REFERENCES "user" (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE nexus_raw_data');
        $this->addSql('DROP TABLE user_access_token');
        $this->addSql('DROP TABLE "user"');
    }
}
