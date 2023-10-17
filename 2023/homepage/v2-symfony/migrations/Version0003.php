<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version0003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add column for pointer type (temporary/permanent)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document ADD permanent BOOLEAN DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document DROP permanent');
    }
}
