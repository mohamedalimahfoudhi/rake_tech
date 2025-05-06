-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 05:44 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jaw12`
--

-- --------------------------------------------------------

--
-- Table structure for table `billet`
--

CREATE TABLE `billet` (
  `ID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `dateAchat` datetime DEFAULT NULL,
  `prix` float DEFAULT NULL,
  `typeBillet` enum('Gradin','Virage','VIP') DEFAULT NULL,
  `statut` enum('Valide','Annulé','Non valide') DEFAULT 'Valide',
  `quantite` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billet`
--

INSERT INTO `billet` (`ID`, `eventID`, `dateAchat`, `prix`, `typeBillet`, `statut`, `quantite`) VALUES
(11, 2, '2025-04-19 02:20:00', 1000, 'Virage', 'Valide', 12),
(13, 5, '2025-04-23 00:51:04', 15, 'Virage', 'Valide', 100);

-- --------------------------------------------------------

--
-- Table structure for table `emprunt`
--

CREATE TABLE `emprunt` (
  `empruntID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `materielID` int(11) DEFAULT NULL,
  `dateEmprunt` date NOT NULL,
  `dateRetour` date NOT NULL,
  `statutEmprunt` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emprunt`
--

INSERT INTO `emprunt` (`empruntID`, `userID`, `materielID`, `dateEmprunt`, `dateRetour`, `statutEmprunt`) VALUES
(2, 13, 1, '2025-02-11', '2025-02-20', 'Active'),
(3, 11, 3, '2025-03-06', '2025-03-13', 'ACTIVE'),
(4, 11, 3, '2025-03-27', '2025-03-27', 'PENDING'),
(5, 12, 3, '2025-04-16', '2025-04-30', 'PENDING');

-- --------------------------------------------------------

--
-- Table structure for table `evenement`
--

CREATE TABLE `evenement` (
  `ID` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `type` enum('TERRAIN','PADDEL') DEFAULT NULL,
  `recompense` varchar(255) DEFAULT NULL,
  `statut` enum('En cours','Terminé','Annulé') DEFAULT NULL,
  `participantsMax` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evenement`
--

INSERT INTO `evenement` (`ID`, `nom`, `details`, `dateDebut`, `dateFin`, `type`, `recompense`, `statut`, `participantsMax`) VALUES
(1, 'Hackathon 2025', 'Compétition de programmation', '2025-03-01', '2025-03-03', '', '5000$', '', 100),
(2, 'Conférence IA', 'Discussion sur l\'intelligence artificielle', '2025-04-15', '2025-04-17', '', 'Certificat', '', 200),
(3, 'Startup Challenge', 'Concours de startups innovantes', '2025-05-10', '2025-05-12', '', '10000$', '', 50),
(5, 'Journée des Sciences', 'Événement scientifique pour étudiants', '2025-07-05', '2025-07-05', '', 'Trophée', 'Annulé', 300),
(6, 'Tennis Tournament 2024', 'Annual tennis tournament', '2025-02-19', '2025-02-20', '', 'Trophy + Prize Money', 'En cours', 32),
(7, 'takwira', 'takwira sobheya', '2002-12-15', '2002-12-16', '', '100', 'En cours', 10),
(8, 'TAKWIRA', 'ZEZEZEZE', '2025-02-21', '2025-02-22', '', '1500', 'Terminé', 150),
(9, 'vdfcv', 'cxvxvcxvcxv', '2025-03-01', '2025-03-08', '', NULL, NULL, 120),
(10, 'qsdsqd', 'sqddsqdqsdq', '2025-03-07', '2025-03-14', '', NULL, 'En cours', 1500),
(11, 'sqddsqdsqd', 'dsqdsqdqsd', '2025-03-14', '2025-03-29', 'TERRAIN', NULL, 'En cours', 1500);

-- --------------------------------------------------------

--
-- Table structure for table `jointable`
--

CREATE TABLE `jointable` (
  `userID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `userRoleInEvent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jointable`
--

INSERT INTO `jointable` (`userID`, `eventID`, `userRoleInEvent`) VALUES
(11, 1, 'participant'),
(11, 2, 'participant');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `maintenanceID` int(11) NOT NULL,
  `materielID` int(11) DEFAULT NULL,
  `dateMaintenance` date NOT NULL,
  `description` text NOT NULL,
  `statutMaintenance` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`maintenanceID`, `materielID`, `dateMaintenance`, `description`, `statutMaintenance`) VALUES
(1, 1, '2025-02-12', 'dsqddqd', 'Scheduled'),
(3, 3, '2025-03-14', 'Automatic maintenance after extended loan period', 'PENDING'),
(4, 3, '2025-05-01', 'Automatic maintenance after extended loan period', 'PENDING');

-- --------------------------------------------------------

--
-- Table structure for table `materiel`
--

CREATE TABLE `materiel` (
  `ID` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `typeSport` varchar(255) DEFAULT NULL,
  `prix` float DEFAULT NULL,
  `dateReservation` date DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `ownerType` enum('Club','Fédération','Privé') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materiel`
--

INSERT INTO `materiel` (`ID`, `type`, `typeSport`, `prix`, `dateReservation`, `statut`, `ownerType`) VALUES
(1, 'dgfdgfdg', 'baff', 1500, '2025-02-19', 'Available', 'Fédération'),
(3, 'bfgh', 'hggfhgfh', 1500, '2025-03-01', 'Reserved', 'Club'),
(4, 'dfdfdf', 'dfdfdf', 10, '2025-02-28', 'Available', 'Club');

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `ID` int(11) NOT NULL,
  `utilisateurID` int(11) NOT NULL,
  `dateReservation` datetime DEFAULT current_timestamp(),
  `statut` enum('Confirmée','Annulée') DEFAULT 'Confirmée',
  `type` enum('TERRAIN','BILLET') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`ID`, `utilisateurID`, `dateReservation`, `statut`, `type`) VALUES
(17, 12, '2025-02-20 20:05:16', 'Annulée', 'BILLET'),
(18, 5, '2025-02-20 12:00:00', 'Confirmée', 'TERRAIN'),
(20, 16, '2025-02-20 20:28:50', 'Confirmée', 'BILLET'),
(21, 5, '2025-02-20 12:00:00', 'Confirmée', 'TERRAIN'),
(22, 15, '2025-02-22 12:00:00', 'Confirmée', 'TERRAIN'),
(23, 17, '2025-02-21 12:00:00', 'Confirmée', 'TERRAIN'),
(24, 15, '2025-02-21 00:34:45', 'Confirmée', 'BILLET'),
(25, 17, '2025-02-21 12:00:00', 'Confirmée', 'TERRAIN'),
(26, 5, '2025-02-21 12:00:00', 'Confirmée', 'TERRAIN'),
(27, 11, '2025-02-27 00:00:00', 'Confirmée', NULL),
(28, 12, '2025-02-15 00:00:00', 'Confirmée', NULL),
(29, 5, '2025-02-28 00:00:00', 'Confirmée', NULL),
(30, 12, '2025-02-28 00:00:00', 'Annulée', NULL),
(31, 5, '2025-03-01 00:00:00', 'Confirmée', NULL),
(32, 5, '2025-02-28 12:00:00', 'Confirmée', 'TERRAIN'),
(33, 12, '2025-02-28 12:00:00', 'Confirmée', 'TERRAIN'),
(42, 11, '2025-02-28 12:00:00', 'Confirmée', 'TERRAIN'),
(43, 11, '2025-02-28 08:08:18', 'Confirmée', 'BILLET'),
(44, 5, '2025-03-07 12:00:00', 'Confirmée', 'TERRAIN');

-- --------------------------------------------------------

--
-- Table structure for table `reservationbillet`
--

CREATE TABLE `reservationbillet` (
  `reservationID` int(11) NOT NULL,
  `billetID` int(11) NOT NULL,
  `nombreBillet` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservationmateriel`
--

CREATE TABLE `reservationmateriel` (
  `ID` int(11) NOT NULL,
  `materielID` int(11) DEFAULT NULL,
  `dateReservation` date DEFAULT NULL,
  `statut` enum('Confirmée','Annulée') DEFAULT NULL,
  `reservationID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservationsiege`
--

CREATE TABLE `reservationsiege` (
  `reservationID` int(11) NOT NULL,
  `siegeID` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservationterrain`
--

CREATE TABLE `reservationterrain` (
  `ID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `terrainID` int(11) DEFAULT NULL,
  `dateReservation` date DEFAULT NULL,
  `heureReservation` time DEFAULT NULL,
  `statut` enum('Confirmée','Annulée') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservationterrain`
--

INSERT INTO `reservationterrain` (`ID`, `userID`, `terrainID`, `dateReservation`, `heureReservation`, `statut`) VALUES
(19, 5, 2, '2025-02-20', '12:00:00', 'Confirmée'),
(21, 5, 2, '2025-02-20', '12:00:00', 'Confirmée'),
(22, 15, 2, '2025-02-22', '12:00:00', 'Confirmée'),
(23, 17, 2, '2025-02-21', '12:00:00', 'Confirmée'),
(25, 17, 5, '2025-02-21', '12:00:00', 'Confirmée'),
(26, 5, 5, '2025-02-21', '12:00:00', 'Confirmée'),
(32, 5, 1, '2025-02-28', '12:00:00', 'Confirmée'),
(33, 12, 1, '2025-02-28', '12:00:00', 'Confirmée'),
(34, 11, 1, '2025-02-28', '12:00:00', 'Confirmée'),
(44, 5, 1, '2025-03-07', '12:00:00', 'Confirmée');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `roleID` int(11) NOT NULL,
  `roleNom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`roleID`, `roleNom`) VALUES
(1, 'ADMIN'),
(2, 'USER'),
(3, 'USER');

-- --------------------------------------------------------

--
-- Table structure for table `terrain`
--

CREATE TABLE `terrain` (
  `courtID` int(11) NOT NULL,
  `type` enum('TERRAIN','PADDEL') DEFAULT NULL,
  `localisation` text DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL,
  `statut` enum('Disponible','Réservé','Maintenance') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terrain`
--

INSERT INTO `terrain` (`courtID`, `type`, `localisation`, `capacite`, `statut`) VALUES
(1, 'PADDEL', 'gafsa', 1500, 'Disponible'),
(2, '', 'GAFSA', 1500, 'Réservé'),
(5, '', 'dqsdsqd', 1500, 'Disponible'),
(6, '', 'zaezae', 150, 'Réservé'),
(7, 'PADDEL', 'fdfsdf', 1500, 'Disponible');

-- --------------------------------------------------------

--
-- Table structure for table `terrainsiege`
--

CREATE TABLE `terrainsiege` (
  `siegeID` int(11) NOT NULL,
  `terrainID` int(11) DEFAULT NULL,
  `rangee` varchar(5) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `statut` enum('Disponible','Réservé','Maintenance') DEFAULT 'Disponible',
  `prix` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terrainsiege`
--

INSERT INTO `terrainsiege` (`siegeID`, `terrainID`, `rangee`, `numero`, `statut`, `prix`) VALUES
(16, 1, '1', '1', 'Disponible', 25.00),
(17, 1, '1', '2', 'Disponible', 25.00),
(18, 1, '1', '3', 'Disponible', 25.00),
(19, 1, '1', '4', 'Disponible', 25.00),
(20, 1, '1', '5', 'Disponible', 25.00),
(21, 1, '2', '1', 'Disponible', 30.00),
(22, 1, '2', '2', 'Disponible', 30.00),
(23, 1, '2', '3', 'Disponible', 30.00),
(24, 1, '2', '4', 'Disponible', 30.00),
(25, 1, '2', '5', 'Disponible', 30.00),
(26, 1, '3', '1', 'Disponible', 35.00),
(27, 1, '3', '2', 'Disponible', 35.00),
(28, 1, '3', '3', 'Disponible', 35.00),
(29, 1, '3', '4', 'Disponible', 35.00),
(30, 1, '3', '5', 'Disponible', 35.00);

--
-- Triggers `terrainsiege`
--
DELIMITER $$
CREATE TRIGGER `update_terrain_status_after_seat_available` AFTER UPDATE ON `terrainsiege` FOR EACH ROW BEGIN
    DECLARE reserved_seats INT;
    
    -- Count reserved seats for the terrain
    SELECT COUNT(CASE WHEN statut = 'Réservé' THEN 1 END)
    INTO reserved_seats
    FROM terrain_siege
    WHERE terrainID = NEW.terrainID;
    
    -- If no seats are reserved, update terrain status
    IF reserved_seats = 0 THEN
        UPDATE terrain
        SET statut = 'Disponible'
        WHERE courtID = NEW.terrainID;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_terrain_status_after_seat_reservation` AFTER UPDATE ON `terrainsiege` FOR EACH ROW BEGIN
    DECLARE total_seats INT;
    DECLARE reserved_seats INT;
    
    -- Count total and reserved seats for the terrain
    SELECT COUNT(*), COUNT(CASE WHEN statut = 'Réservé' THEN 1 END)
    INTO total_seats, reserved_seats
    FROM terrain_siege
    WHERE terrainID = NEW.terrainID;
    
    -- If all seats are reserved, update terrain status
    IF total_seats = reserved_seats THEN
        UPDATE terrain
        SET statut = 'Occupé'
        WHERE courtID = NEW.terrainID;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tournoi`
--

CREATE TABLE `tournoi` (
  `ID` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `typeSport` varchar(100) NOT NULL,
  `statut` enum('Prévu','En cours','Terminé','Annulé') DEFAULT 'Prévu',
  `recompense` varchar(255) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `ID` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `numeroTelephone` varchar(50) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `photoProfil` varchar(255) DEFAULT NULL,
  `roleID` int(11) DEFAULT NULL,
  `nomOrganisation` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`ID`, `email`, `motdepasse`, `genre`, `prenom`, `nom`, `numeroTelephone`, `adresse`, `photoProfil`, `roleID`, `nomOrganisation`) VALUES
(5, 'aziz@aziz.com', '123456789', 'aziz', 'aziz', 'baffoun', '98765432', 'atete', 'erer', 1, '150'),
(11, 'AZIZ@TEST.COM', '123456789', 'M', 'Ali', 'Ben Salem', '123456789', 'Tunis, Tunisie', 'photo1.jpg', 2, 'Company A'),
(12, 'user2@example.com', 'password123', 'F', 'Sara', 'Mahmoud', '987654321', 'Sfax, Tunisie', 'photo2.jpg', 1, 'Company B'),
(13, 'user3@example.com', 'password123', 'M', 'Omar', 'Trabelsi', '1122334455', 'Sousse, Tunisie', 'photo3.jpg', 1, 'Company C'),
(14, 'user4@example.com', 'password123', 'F', 'Nour', 'Jaziri', '2233445566', 'Nabeul, Tunisie', 'photo4.jpg', 1, 'Company D'),
(15, 'user5@example.com', 'password123', 'M', 'Karim', 'Dridi', '3344556677', 'Monastir, Tunisie', 'photo5.jpg', 1, 'Company E'),
(16, 'user6@example.com', 'password123', 'F', 'Mouna', 'Gharbi', '4455667788', 'Bizerte, Tunisie', 'photo6.jpg', 1, 'Company F'),
(17, 'user7@example.com', 'password123', 'M', 'Ahmed', 'Saidi', '5566778899', 'Gabès, Tunisie', 'photo7.jpg', 1, 'Company G'),
(18, 'user8@example.com', 'password123', 'F', 'Rania', 'Khelifi', '6677889900', 'Djerba, Tunisie', 'photo8.jpg', 1, 'Company H'),
(19, 'user9@example.com', 'password123', 'M', 'Walid', 'Boussetta', '7788990011', 'Kairouan, Tunisie', 'photo9.jpg', 1, 'Company I'),
(20, 'user10@example.com', 'password123', 'F', 'Ines', 'Zouari', '8899001122', 'Gafsa, Tunisie', 'photo10.jpg', 1, 'Company J');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billet`
--
ALTER TABLE `billet`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `emprunt`
--
ALTER TABLE `emprunt`
  ADD PRIMARY KEY (`empruntID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `materielID` (`materielID`);

--
-- Indexes for table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `jointable`
--
ALTER TABLE `jointable`
  ADD PRIMARY KEY (`userID`,`eventID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`maintenanceID`),
  ADD KEY `materielID` (`materielID`);

--
-- Indexes for table `materiel`
--
ALTER TABLE `materiel`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `utilisateurID` (`utilisateurID`);

--
-- Indexes for table `reservationbillet`
--
ALTER TABLE `reservationbillet`
  ADD PRIMARY KEY (`reservationID`,`billetID`),
  ADD KEY `billetID` (`billetID`);

--
-- Indexes for table `reservationmateriel`
--
ALTER TABLE `reservationmateriel`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `materielID` (`materielID`),
  ADD KEY `reservationID` (`reservationID`);

--
-- Indexes for table `reservationsiege`
--
ALTER TABLE `reservationsiege`
  ADD PRIMARY KEY (`reservationID`,`siegeID`),
  ADD KEY `siegeID` (`siegeID`);

--
-- Indexes for table `reservationterrain`
--
ALTER TABLE `reservationterrain`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `terrainID` (`terrainID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`roleID`);

--
-- Indexes for table `terrain`
--
ALTER TABLE `terrain`
  ADD PRIMARY KEY (`courtID`);

--
-- Indexes for table `terrainsiege`
--
ALTER TABLE `terrainsiege`
  ADD PRIMARY KEY (`siegeID`),
  ADD KEY `terrainID` (`terrainID`);

--
-- Indexes for table `tournoi`
--
ALTER TABLE `tournoi`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `roleID` (`roleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billet`
--
ALTER TABLE `billet`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `emprunt`
--
ALTER TABLE `emprunt`
  MODIFY `empruntID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `maintenanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `materiel`
--
ALTER TABLE `materiel`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `reservationmateriel`
--
ALTER TABLE `reservationmateriel`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservationterrain`
--
ALTER TABLE `reservationterrain`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `terrain`
--
ALTER TABLE `terrain`
  MODIFY `courtID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `terrainsiege`
--
ALTER TABLE `terrainsiege`
  MODIFY `siegeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tournoi`
--
ALTER TABLE `tournoi`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billet`
--
ALTER TABLE `billet`
  ADD CONSTRAINT `billet_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `evenement` (`ID`);

--
-- Constraints for table `emprunt`
--
ALTER TABLE `emprunt`
  ADD CONSTRAINT `emprunt_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `utilisateur` (`ID`),
  ADD CONSTRAINT `emprunt_ibfk_2` FOREIGN KEY (`materielID`) REFERENCES `materiel` (`ID`);

--
-- Constraints for table `jointable`
--
ALTER TABLE `jointable`
  ADD CONSTRAINT `jointable_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `utilisateur` (`ID`),
  ADD CONSTRAINT `jointable_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `evenement` (`ID`);

--
-- Constraints for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`materielID`) REFERENCES `materiel` (`ID`);

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`utilisateurID`) REFERENCES `utilisateur` (`ID`);

--
-- Constraints for table `reservationbillet`
--
ALTER TABLE `reservationbillet`
  ADD CONSTRAINT `reservationbillet_ibfk_1` FOREIGN KEY (`reservationID`) REFERENCES `reservation` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservationbillet_ibfk_2` FOREIGN KEY (`billetID`) REFERENCES `billet` (`ID`);

--
-- Constraints for table `reservationmateriel`
--
ALTER TABLE `reservationmateriel`
  ADD CONSTRAINT `reservationmateriel_ibfk_2` FOREIGN KEY (`materielID`) REFERENCES `materiel` (`ID`),
  ADD CONSTRAINT `reservationmateriel_ibfk_3` FOREIGN KEY (`reservationID`) REFERENCES `reservation` (`ID`);

--
-- Constraints for table `reservationsiege`
--
ALTER TABLE `reservationsiege`
  ADD CONSTRAINT `reservationsiege_ibfk_1` FOREIGN KEY (`reservationID`) REFERENCES `reservationterrain` (`ID`),
  ADD CONSTRAINT `reservationsiege_ibfk_2` FOREIGN KEY (`siegeID`) REFERENCES `terrainsiege` (`siegeID`);

--
-- Constraints for table `reservationterrain`
--
ALTER TABLE `reservationterrain`
  ADD CONSTRAINT `reservationterrain_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `utilisateur` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservationterrain_ibfk_2` FOREIGN KEY (`terrainID`) REFERENCES `terrain` (`courtID`);

--
-- Constraints for table `terrainsiege`
--
ALTER TABLE `terrainsiege`
  ADD CONSTRAINT `terrainsiege_ibfk_1` FOREIGN KEY (`terrainID`) REFERENCES `terrain` (`courtID`);

--
-- Constraints for table `tournoi`
--
ALTER TABLE `tournoi`
  ADD CONSTRAINT `tournoi_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `evenement` (`ID`) ON DELETE SET NULL;

--
-- Constraints for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`roleID`) REFERENCES `role` (`roleID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
