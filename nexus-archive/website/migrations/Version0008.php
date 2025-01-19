<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename "nexus_raw_data" table to "page_view", minor redesign of table columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nexus_raw_data RENAME TO page_view');
        $this->addSql('ALTER TABLE page_view RENAME COLUMN submitter_id TO owner_id');
        $this->addSql('ALTER TABLE page_view ALTER request_started_at DROP NOT NULL');
        $this->addSql('ALTER TABLE page_view ALTER response_completed_at DROP NOT NULL');
        $this->addSql('ALTER INDEX nexus_raw_data_sorting_idx RENAME TO page_view_sorting_idx');
        $this->addSql('ALTER INDEX nexus_raw_data_submitter_idx RENAME TO page_view_owner_idx');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX page_view_owner_idx RENAME TO nexus_raw_data_submitter_idx');
        $this->addSql('ALTER INDEX page_view_sorting_idx RENAME TO nexus_raw_data_sorting_idx');
        $this->addSql('ALTER TABLE page_view ALTER response_completed_at SET NOT NULL');
        $this->addSql('ALTER TABLE page_view ALTER request_started_at SET NOT NULL');
        $this->addSql('ALTER TABLE page_view RENAME COLUMN owner_id TO submitter_id');
        $this->addSql('ALTER TABLE page_view RENAME TO nexus_raw_data');
    }
}
