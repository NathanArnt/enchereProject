<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125095736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation ADD le_user_id INT DEFAULT NULL, ADD la_enchere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F88A1A5E2 FOREIGN KEY (le_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FC80D2A2F FOREIGN KEY (la_enchere_id) REFERENCES enchere (id)');
        $this->addSql('CREATE INDEX IDX_AB55E24F88A1A5E2 ON participation (le_user_id)');
        $this->addSql('CREATE INDEX IDX_AB55E24FC80D2A2F ON participation (la_enchere_id)');
        $this->addSql('ALTER TABLE produit ADD la_enchere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27C80D2A2F FOREIGN KEY (la_enchere_id) REFERENCES enchere (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29A5EC27C80D2A2F ON produit (la_enchere_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F88A1A5E2');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FC80D2A2F');
        $this->addSql('DROP INDEX IDX_AB55E24F88A1A5E2 ON participation');
        $this->addSql('DROP INDEX IDX_AB55E24FC80D2A2F ON participation');
        $this->addSql('ALTER TABLE participation DROP le_user_id, DROP la_enchere_id');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27C80D2A2F');
        $this->addSql('DROP INDEX UNIQ_29A5EC27C80D2A2F ON produit');
        $this->addSql('ALTER TABLE produit DROP la_enchere_id');
    }
}
