<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903195059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Cria a tabela owner primeiro (se ainda não existir)
        $this->addSql('CREATE TABLE owner (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Cria a tabela investment com a relação ManyToOne para owner
        $this->addSql('CREATE TABLE investment (
            id INT AUTO_INCREMENT NOT NULL,
            owner_id INT NOT NULL,
            creation_date DATETIME NOT NULL,
            investment_value DOUBLE PRECISION NOT NULL,
            PRIMARY KEY(id),
            CONSTRAINT FK_INVESTMENT_OWNER FOREIGN KEY (owner_id) REFERENCES owner (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE investment');
        $this->addSql('DROP TABLE owner');
    }
}
