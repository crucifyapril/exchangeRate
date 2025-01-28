<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250127175318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE currency_rates (id INT AUTO_INCREMENT NOT NULL, currency_code VARCHAR(3) NOT NULL, rate NUMERIC(10, 4) NOT NULL, date DATETIME NOT NULL, trend VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX unique_currency_date ON currency_rates (currency_code, date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currency_rates');
        $this->addSql('DROP INDEX unique_currency_date ON currency_rates');
    }
}
