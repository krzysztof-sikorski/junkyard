<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create document table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            sql: <<<'SQL'
                CREATE TABLE document (
                    id UUID NOT NULL,
                    parent_id UUID DEFAULT NULL,
                    pointer_target_id UUID DEFAULT NULL,
                    created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                    updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                    deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                    slug TEXT DEFAULT NULL,
                    path TEXT DEFAULT NULL,
                    type TEXT NOT NULL,
                    page_title TEXT DEFAULT NULL,
                    page_content TEXT DEFAULT NULL,
                    file_filename TEXT DEFAULT NULL,
                    file_storage_path TEXT DEFAULT NULL,
                    PRIMARY KEY(id)
                )
                SQL,
        );
        $this->addSql(sql: 'CREATE INDEX document_type_idx ON document (type)');
        $this->addSql(sql: 'CREATE INDEX document_parent_idx ON document (parent_id)');
        $this->addSql(sql: 'CREATE INDEX document_path_idx ON document (path)');
        $this->addSql(sql: 'CREATE INDEX document_pointer_target_idx ON document (pointer_target_id)');
        $this->addSql(sql: 'COMMENT ON COLUMN document.id IS \'(DC2Type:uuid)\'');
        $this->addSql(sql: 'COMMENT ON COLUMN document.parent_id IS \'(DC2Type:uuid)\'');
        $this->addSql(sql: 'COMMENT ON COLUMN document.pointer_target_id IS \'(DC2Type:uuid)\'');
        $this->addSql(sql: 'COMMENT ON COLUMN document.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql(sql: 'COMMENT ON COLUMN document.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql(sql: 'COMMENT ON COLUMN document.deleted_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql(
            sql: <<<'SQL'
                ALTER TABLE document
                ADD CONSTRAINT document_parent_fkey
                FOREIGN KEY (parent_id) REFERENCES document (id)
                ON DELETE SET NULL
                NOT DEFERRABLE INITIALLY IMMEDIATE
                SQL,
        );
        $this->addSql(
            sql: <<<'SQL'
                ALTER TABLE document
                ADD CONSTRAINT document_pointer_target_fkey
                FOREIGN KEY (pointer_target_id) REFERENCES document (id)
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
                SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sql: 'DROP TABLE document CASCADE');
    }
}
