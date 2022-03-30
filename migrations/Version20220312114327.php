<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220312114327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire ADD coach_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC3C105691 FOREIGN KEY (coach_id) REFERENCES coach (id)');
        $this->addSql('CREATE INDEX IDX_67F068BC3C105691 ON commentaire (coach_id)');
        $this->addSql('ALTER TABLE programme ADD coach_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE programme ADD CONSTRAINT FK_3DDCB9FF3C105691 FOREIGN KEY (coach_id) REFERENCES coach (id)');
        $this->addSql('CREATE INDEX IDX_3DDCB9FF3C105691 ON programme (coach_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC3C105691');
        $this->addSql('ALTER TABLE programme DROP FOREIGN KEY FK_3DDCB9FF3C105691');
        $this->addSql('DROP INDEX IDX_67F068BC3C105691 ON commentaire');
        $this->addSql('ALTER TABLE commentaire DROP coach_id');
        $this->addSql('DROP INDEX IDX_3DDCB9FF3C105691 ON programme');
        $this->addSql('ALTER TABLE programme DROP coach_id');
    }
}
