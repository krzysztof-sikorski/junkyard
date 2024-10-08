<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make document.path unique';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX document_path_idx');
        $this->addSql('CREATE UNIQUE INDEX document_path_idx ON document (path)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX document_path_idx');
        $this->addSql('CREATE INDEX document_path_idx ON document (path)');
    }
}
