<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216202705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE multibattle (id INT AUTO_INCREMENT NOT NULL, ally_trainer_id INT DEFAULT NULL, rival_trainer_id INT DEFAULT NULL, ally_pokearray LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', rival_pokearray LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', result LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_CE9F76745450F74A (ally_trainer_id), INDEX IDX_CE9F76749F65F960 (rival_trainer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE multibattle ADD CONSTRAINT FK_CE9F76745450F74A FOREIGN KEY (ally_trainer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE multibattle ADD CONSTRAINT FK_CE9F76749F65F960 FOREIGN KEY (rival_trainer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user ADD open_battle TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE multibattle DROP FOREIGN KEY FK_CE9F76745450F74A');
        $this->addSql('ALTER TABLE multibattle DROP FOREIGN KEY FK_CE9F76749F65F960');
        $this->addSql('DROP TABLE multibattle');
        $this->addSql('ALTER TABLE `user` DROP open_battle');
    }
}
