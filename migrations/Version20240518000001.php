<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter le champ codeUnique à la table billet
 */
final class Version20240518000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter un champ de code unique à la table des billets';
    }

    public function up(Schema $schema): void
    {
        // Ajouter le champ codeUnique à la table billet
        $this->addSql('ALTER TABLE billet ADD codeUnique VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Supprimer le champ codeUnique
        $this->addSql('ALTER TABLE billet DROP codeUnique');
    }
} 