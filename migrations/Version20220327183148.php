<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220327183148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE disponibilite (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, coach_id INT NOT NULL, date DATETIME NOT NULL, etat VARCHAR(255) NOT NULL, canal VARCHAR(255) NOT NULL, INDEX IDX_2CBACE2FA76ED395 (user_id), INDEX IDX_2CBACE2F3C105691 (coach_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE disponibilite ADD CONSTRAINT FK_2CBACE2FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE disponibilite ADD CONSTRAINT FK_2CBACE2F3C105691 FOREIGN KEY (coach_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE programme CHANGE description description VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE disponibilite');
        $this->addSql('ALTER TABLE programme CHANGE description description TEXT NOT NULL');
    }
}
