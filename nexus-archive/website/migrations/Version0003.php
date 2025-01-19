<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize names for indices enforced by Doctrine';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER INDEX username_uniq RENAME TO user_username_uniq');
        $this->addSql('ALTER INDEX idx_366ea16a7e3c61f9 RENAME TO user_access_token_owner_idx');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX user_access_token_owner_idx RENAME TO idx_366ea16a7e3c61f9');
        $this->addSql('ALTER INDEX user_username_uniq RENAME TO username_uniq');
    }
}
