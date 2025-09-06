<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250906140858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE investment ADD withdrawn_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE investment RENAME INDEX fk_investment_owner TO IDX_43CA0AD67E3C61F9');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE investment DROP withdrawn_at');
        $this->addSql('ALTER TABLE investment RENAME INDEX idx_43ca0ad67e3c61f9 TO FK_INVESTMENT_OWNER');
    }
}
