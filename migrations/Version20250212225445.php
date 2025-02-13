<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212225445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pokedex DROP level, DROP strength');
        $this->addSql('ALTER TABLE pokedex_pokemon DROP FOREIGN KEY FK_BD0379D5372A5D14');
        $this->addSql('ALTER TABLE pokedex_pokemon DROP FOREIGN KEY FK_BD0379D52FE71C3E');
        $this->addSql('ALTER TABLE pokedex_pokemon ADD id INT AUTO_INCREMENT NOT NULL, ADD level INT NOT NULL, ADD strength INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE pokedex_pokemon ADD CONSTRAINT FK_BD0379D5372A5D14 FOREIGN KEY (pokedex_id) REFERENCES pokedex (id)');
        $this->addSql('ALTER TABLE pokedex_pokemon ADD CONSTRAINT FK_BD0379D52FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pokedex ADD level INT DEFAULT NULL, ADD strength INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pokedex_pokemon MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE pokedex_pokemon DROP FOREIGN KEY FK_BD0379D5372A5D14');
        $this->addSql('ALTER TABLE pokedex_pokemon DROP FOREIGN KEY FK_BD0379D52FE71C3E');
        $this->addSql('DROP INDEX `PRIMARY` ON pokedex_pokemon');
        $this->addSql('ALTER TABLE pokedex_pokemon DROP id, DROP level, DROP strength');
        $this->addSql('ALTER TABLE pokedex_pokemon ADD CONSTRAINT FK_BD0379D5372A5D14 FOREIGN KEY (pokedex_id) REFERENCES pokedex (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pokedex_pokemon ADD CONSTRAINT FK_BD0379D52FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pokedex_pokemon ADD PRIMARY KEY (pokedex_id, pokemon_id)');
    }
}
