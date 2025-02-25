<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225110758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bet_card (bet_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_33C24BEFD871DC26 (bet_id), INDEX IDX_33C24BEF4ACC9A20 (card_id), PRIMARY KEY(bet_id, card_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bet_card ADD CONSTRAINT FK_33C24BEFD871DC26 FOREIGN KEY (bet_id) REFERENCES bet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bet_card ADD CONSTRAINT FK_33C24BEF4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card ADD base_points INT NOT NULL, ADD `condition` VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bet_card DROP FOREIGN KEY FK_33C24BEFD871DC26');
        $this->addSql('ALTER TABLE bet_card DROP FOREIGN KEY FK_33C24BEF4ACC9A20');
        $this->addSql('DROP TABLE bet_card');
        $this->addSql('ALTER TABLE card DROP base_points, DROP `condition`');
    }
}
