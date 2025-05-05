<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501142231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE billet (ID INT AUTO_INCREMENT NOT NULL, dateAchat DATETIME DEFAULT NULL, prix DOUBLE PRECISION DEFAULT NULL, typeBillet VARCHAR(255) DEFAULT NULL, statut VARCHAR(20) DEFAULT NULL, quantite INT DEFAULT NULL, eventID INT DEFAULT NULL, INDEX IDX_1F034AF610409BA4 (eventID), PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE emprunt (userid INT DEFAULT NULL, empruntID INT AUTO_INCREMENT NOT NULL, dateEmprunt DATE NOT NULL, dateRetour DATE NOT NULL, statutEmprunt VARCHAR(255) DEFAULT 'NULL', materielID INT DEFAULT NULL, INDEX IDX_364071D7B2E8733F (materielID), INDEX IDX_364071D7F132696E (userid), PRIMARY KEY(empruntID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, terrain_id INT DEFAULT NULL, details VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, reward VARCHAR(255) NOT NULL, INDEX IDX_3BAE0AA78A2D8B41 (terrain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE maintenance (maintenanceID INT AUTO_INCREMENT NOT NULL, dateMaintenance DATE NOT NULL, description TEXT NOT NULL, statutMaintenance VARCHAR(255) DEFAULT 'NULL', materielID INT DEFAULT NULL, INDEX IDX_2F84F8E9B2E8733F (materielID), PRIMARY KEY(maintenanceID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE materiel (ID INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) DEFAULT NULL, typeSport VARCHAR(255) DEFAULT NULL, prix DOUBLE PRECISION DEFAULT NULL, dateReservation DATE DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, ownerType VARCHAR(255) DEFAULT NULL, PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (utilisateurid INT DEFAULT NULL, tournoiid INT DEFAULT NULL, ID INT AUTO_INCREMENT NOT NULL, dateReservation DATETIME DEFAULT NULL, statut VARCHAR(20) DEFAULT '''Confirmée''', type VARCHAR(20) DEFAULT NULL, INDEX IDX_42C84955A46AB7D5 (utilisateurid), INDEX IDX_42C849554696CC5F (tournoiid), PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation_billet (reservationID INT NOT NULL, billetID INT NOT NULL, INDEX IDX_57F2036DD91F71D7 (reservationID), INDEX IDX_57F2036DC53C928C (billetID), PRIMARY KEY(reservationID, billetID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE role (roleID INT AUTO_INCREMENT NOT NULL, roleNom VARCHAR(255) NOT NULL, PRIMARY KEY(roleID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE terrain (id INT AUTO_INCREMENT NOT NULL, court_type VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tournoi (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, dateDebut DATE NOT NULL, dateFin DATE NOT NULL, lieu VARCHAR(255) NOT NULL, typeSport VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, recompense VARCHAR(255) NOT NULL, eventId INT DEFAULT NULL, INDEX IDX_18AFD9DF2B2EBB6C (eventId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tournoi_participants (tournois INT NOT NULL, participants INT NOT NULL, INDEX IDX_324B5587D7AAF97 (tournois), INDEX IDX_324B558771697092 (participants), PRIMARY KEY(tournois, participants)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE utilisateur (ID INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, motdepasse VARCHAR(255) NOT NULL, genre VARCHAR(50) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, numeroTelephone VARCHAR(50) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, photoProfil VARCHAR(255) DEFAULT NULL, nomOrganisation VARCHAR(255) DEFAULT NULL, roleID INT DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), INDEX IDX_1D1C63B383ACDD40 (roleID), PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE billet ADD CONSTRAINT FK_1F034AF610409BA4 FOREIGN KEY (eventID) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emprunt ADD CONSTRAINT FK_364071D7B2E8733F FOREIGN KEY (materielID) REFERENCES materiel (ID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emprunt ADD CONSTRAINT FK_364071D7F132696E FOREIGN KEY (userid) REFERENCES utilisateur (ID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA78A2D8B41 FOREIGN KEY (terrain_id) REFERENCES terrain (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE maintenance ADD CONSTRAINT FK_2F84F8E9B2E8733F FOREIGN KEY (materielID) REFERENCES materiel (ID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A46AB7D5 FOREIGN KEY (utilisateurid) REFERENCES utilisateur (ID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C849554696CC5F FOREIGN KEY (tournoiid) REFERENCES tournoi (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_billet ADD CONSTRAINT FK_57F2036DD91F71D7 FOREIGN KEY (reservationID) REFERENCES reservation (ID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_billet ADD CONSTRAINT FK_57F2036DC53C928C FOREIGN KEY (billetID) REFERENCES billet (ID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournoi ADD CONSTRAINT FK_18AFD9DF2B2EBB6C FOREIGN KEY (eventId) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournoi_participants ADD CONSTRAINT FK_324B5587D7AAF97 FOREIGN KEY (tournois) REFERENCES tournoi (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournoi_participants ADD CONSTRAINT FK_324B558771697092 FOREIGN KEY (participants) REFERENCES utilisateur (ID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B383ACDD40 FOREIGN KEY (roleID) REFERENCES role (roleID)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE billet DROP FOREIGN KEY FK_1F034AF610409BA4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D7B2E8733F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D7F132696E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA78A2D8B41
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE maintenance DROP FOREIGN KEY FK_2F84F8E9B2E8733F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A46AB7D5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554696CC5F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_billet DROP FOREIGN KEY FK_57F2036DD91F71D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_billet DROP FOREIGN KEY FK_57F2036DC53C928C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournoi DROP FOREIGN KEY FK_18AFD9DF2B2EBB6C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournoi_participants DROP FOREIGN KEY FK_324B5587D7AAF97
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournoi_participants DROP FOREIGN KEY FK_324B558771697092
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B383ACDD40
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE billet
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE emprunt
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE maintenance
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE materiel
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation_billet
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE role
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE terrain
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tournoi
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tournoi_participants
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
