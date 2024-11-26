<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125161549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enchere ADD le_produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enchere ADD CONSTRAINT FK_38D1870F2C340150 FOREIGN KEY (le_produit_id) REFERENCES produit (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_38D1870F2C340150 ON enchere (le_produit_id)');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27C80D2A2F');
        $this->addSql('DROP INDEX UNIQ_29A5EC27C80D2A2F ON produit');
        $this->addSql('ALTER TABLE produit DROP la_enchere_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enchere DROP FOREIGN KEY FK_38D1870F2C340150');
        $this->addSql('DROP INDEX UNIQ_38D1870F2C340150 ON enchere');
        $this->addSql('ALTER TABLE enchere DROP le_produit_id');
        $this->addSql('ALTER TABLE produit ADD la_enchere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27C80D2A2F FOREIGN KEY (la_enchere_id) REFERENCES enchere (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29A5EC27C80D2A2F ON produit (la_enchere_id)');
    }
}
