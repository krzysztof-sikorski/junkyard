<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make sure all tables have created_at and last_modified_at columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD last_modified_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now()');
        $this->addSql('ALTER TABLE "user" ALTER last_modified_at DROP DEFAULT');
        $this->addSql("COMMENT ON COLUMN \"user\".last_modified_at IS '(DC2Type:datetimetz_immutable)'");

        $this->addSql(
            'ALTER TABLE user_access_token ADD last_modified_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now()'
        );
        $this->addSql('ALTER TABLE user_access_token ALTER last_modified_at DROP DEFAULT');
        $this->addSql("COMMENT ON COLUMN user_access_token.last_modified_at IS '(DC2Type:datetimetz_immutable)'");

        $this->addSql('DROP INDEX nexus_raw_data_sorting_idx');
        $this->addSql('ALTER TABLE nexus_raw_data RENAME COLUMN submitted_at TO created_at');
        $this->addSql('CREATE INDEX nexus_raw_data_sorting_idx ON nexus_raw_data (created_at, request_started_at, id)');

        $this->addSql(
            'ALTER TABLE nexus_raw_data ADD last_modified_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now()'
        );
        $this->addSql('ALTER TABLE nexus_raw_data ALTER last_modified_at DROP DEFAULT');
        $this->addSql("COMMENT ON COLUMN nexus_raw_data.last_modified_at IS '(DC2Type:datetimetz_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX nexus_raw_data_sorting_idx');
        $this->addSql('ALTER TABLE nexus_raw_data RENAME created_at TO submitted_at');
        $this->addSql(
            'CREATE INDEX nexus_raw_data_sorting_idx ON nexus_raw_data (submitted_at, request_started_at, id)'
        );

        $this->addSql('ALTER TABLE nexus_raw_data DROP last_modified_at');
        $this->addSql('ALTER TABLE user_access_token DROP last_modified_at');
        $this->addSql('ALTER TABLE "user" DROP last_modified_at');
    }
}
