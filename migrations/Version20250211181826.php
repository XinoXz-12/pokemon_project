<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211181826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pokedex_pokemon (pokedex_id INT NOT NULL, pokemon_id INT NOT NULL, INDEX IDX_BD0379D5372A5D14 (pokedex_id), INDEX IDX_BD0379D52FE71C3E (pokemon_id), PRIMARY KEY(pokedex_id, pokemon_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pokedex_pokemon ADD CONSTRAINT FK_BD0379D5372A5D14 FOREIGN KEY (pokedex_id) REFERENCES pokedex (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pokedex_pokemon ADD CONSTRAINT FK_BD0379D52FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pokedex ADD trainer_id INT NOT NULL');
        $this->addSql('ALTER TABLE pokedex ADD CONSTRAINT FK_6336F6A7FB08EDF6 FOREIGN KEY (trainer_id) REFERENCES `user` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6336F6A7FB08EDF6 ON pokedex (trainer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pokedex_pokemon DROP FOREIGN KEY FK_BD0379D5372A5D14');
        $this->addSql('ALTER TABLE pokedex_pokemon DROP FOREIGN KEY FK_BD0379D52FE71C3E');
        $this->addSql('DROP TABLE pokedex_pokemon');
        $this->addSql('ALTER TABLE pokedex DROP FOREIGN KEY FK_6336F6A7FB08EDF6');
        $this->addSql('DROP INDEX UNIQ_6336F6A7FB08EDF6 ON pokedex');
        $this->addSql('ALTER TABLE pokedex DROP trainer_id');
    }
}
