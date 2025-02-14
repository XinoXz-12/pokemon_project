<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214165148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE battle DROP FOREIGN KEY FK_1399173480BF0682');
        $this->addSql('ALTER TABLE battle ADD CONSTRAINT FK_1399173480BF0682 FOREIGN KEY (ally_pokemon_id) REFERENCES pokemon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE battle DROP FOREIGN KEY FK_1399173480BF0682');
        $this->addSql('ALTER TABLE battle ADD CONSTRAINT FK_1399173480BF0682 FOREIGN KEY (ally_pokemon_id) REFERENCES pokedex (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
