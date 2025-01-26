<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250124171223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE products (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            price DECIMAL(10, 2) NOT NULL
        )');

        $this->addSql('CREATE TABLE coupons (
            id SERIAL PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            type VARCHAR(10) NOT NULL CHECK (type IN (\'fixed\', \'percent\')),
            value DECIMAL(10, 2) NOT NULL
        )');

        $this->addSql('CREATE TABLE tax_formats (
            id SERIAL PRIMARY KEY,
            country_code VARCHAR(2) NOT NULL UNIQUE,
            regex_pattern VARCHAR(255) NOT NULL,
            tax_rate DECIMAL(5, 2) NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE coupons');
        $this->addSql('DROP TABLE tax_formats');
    }
}
