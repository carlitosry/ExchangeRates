<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20230328205907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create new table exchange_rate, to save the rates obtain from third parts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE exchange_rate (
                id INT AUTO_INCREMENT NOT NULL,
                base_currency VARCHAR(255) NOT NULL,
                target_currency VARCHAR(255) NOT NULL,
                rate DOUBLE PRECISION NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    /**
     * Just need to execute the following command:
     * `migrations:execute --down 'DoctrineMigrations\Version20230328205907'`
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE exchange_rate');
    }
}
