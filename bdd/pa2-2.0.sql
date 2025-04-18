-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 18 avr. 2025 à 10:10
-- Version du serveur : 8.2.0
-- Version de PHP : 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `pa2`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonces`
--

DROP TABLE IF EXISTS `annonces`;
CREATE TABLE IF NOT EXISTS `annonces` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `ville_depart` varchar(100) DEFAULT NULL,
  `ville_arrivee` varchar(100) DEFAULT NULL,
  `date_annonce` date DEFAULT NULL,
  `statut` enum('en attente','prise en charge','livrée','annulée') NOT NULL DEFAULT 'en attente',
  `taille` float DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `date_livraison_souhaitee` date DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_client` (`id_client`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `annonces`
--

INSERT INTO `annonces` (`id`, `id_client`, `titre`, `description`, `ville_depart`, `ville_arrivee`, `date_annonce`, `statut`, `taille`, `prix`, `date_livraison_souhaitee`, `date_expiration`) VALUES
(4, 13, 'test2', 'test2', '38 Avenue Millies Lacroix, Élancourt', '37 boulevard Amiral Courbet, Orvault', '2025-04-10', '', 56, 25.00, '2025-04-21', '2025-04-21'),
(6, 13, 'test', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ', '38 Avenue Millies Lacroix, Élancourt', '37 boulevard Amiral Courbet, Orvault	', '2025-04-10', '', 56, 15.00, '2025-04-29', '2025-04-29'),
(7, 29, 'Caca de kamil', 'Le caca de kamil comme dit dans le titre ^^', '7 avenue Joffre, 94160 Saint-Mandé', '7 allée soufflot, 92600 Asnières-sur-Seine', '2025-04-17', '', 69, 0.69, '2025-05-02', '2025-04-30'),
(8, 26, 'Livraison Test', 'Cadeau d\'anniversaire', '7 allée soufflot, 92600 Asnières-sur-Seine', '69 Place Napoléon, 56600 Lanester', '2025-04-18', '', 52, 20.00, '2025-08-25', '2025-08-25'),
(9, 103, 'Livraison 1', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ', '7 allée soufflot, 92600 Asnières-sur-Seine', '94 Rue de Strasbourg, 63000 Clermont-ferrand', '2025-04-18', '', 15, 15.00, '2025-04-28', '2025-04-28'),
(11, 103, 'test 2', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ', '7 allée soufflot, 92600 Asnières-sur-Seine', '242 Rue du Faubourg Saint-Antoine, 75012 Paris', '2025-04-18', '', 242, 242.00, '2025-04-25', '2025-04-25');

-- --------------------------------------------------------

--
-- Structure de la table `contrats`
--

DROP TABLE IF EXISTS `contrats`;
CREATE TABLE IF NOT EXISTS `contrats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_commercant` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_commercant` (`id_commercant`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `contrats`
--

INSERT INTO `contrats` (`id`, `id_commercant`, `date_debut`, `date_fin`) VALUES
(1, 1, '2025-03-04', '2028-03-15');

-- --------------------------------------------------------

--
-- Structure de la table `livraisons`
--

DROP TABLE IF EXISTS `livraisons`;
CREATE TABLE IF NOT EXISTS `livraisons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_livreur` int NOT NULL,
  `id_annonce` int NOT NULL,
  `date_prise_en_charge` datetime DEFAULT NULL,
  `date_livraison` datetime DEFAULT NULL,
  `statut` enum('en attente','en cours','livrée','annulée') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_client` (`id_client`),
  KEY `id_livreur` (`id_livreur`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `livraisons`
--

INSERT INTO `livraisons` (`id`, `id_client`, `id_livreur`, `id_annonce`, `date_prise_en_charge`, `date_livraison`, `statut`) VALUES
(12, 103, 104, 11, '2025-04-18 11:34:08', '2025-04-24 11:54:27', 'en cours'),
(13, 103, 104, 9, '2025-04-18 11:54:15', '2025-04-24 11:54:41', 'en cours');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `montant` decimal(10,2) NOT NULL,
  `methode` enum('carte','paypal','virement','espece') NOT NULL,
  `statut` enum('en attente','effectué','échoué') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `montant`, `methode`, `statut`) VALUES
(6, 199.20, 'carte', 'effectué');

-- --------------------------------------------------------

--
-- Structure de la table `prestations`
--

DROP TABLE IF EXISTS `prestations`;
CREATE TABLE IF NOT EXISTS `prestations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `statut` enum('en attente','en cours','terminée','annulée') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role` enum('client','livreur','commercant','prestataire','admin') NOT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `photo_profil` varchar(255) DEFAULT NULL,
  `adresse` text,
  `statut` enum('disponible','en livraison','indisponible') DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `mot_de_passe`, `role`, `date_inscription`, `photo_profil`, `adresse`, `statut`, `type`, `service`) VALUES
(102, 'livraison@livraison.com', 'livraison@livraison.com', '$2y$10$RBHMAZvNUCr8H9uE6bGaROL/Fgrsum7n8Jx9n3ceEyWZh7yLI4O9G', 'livreur', '2025-04-18 11:07:32', NULL, NULL, NULL, NULL, NULL),
(103, 'client@client.com', 'client@client.com', '$2y$10$twg6Rz9/2LKYMTYJ2Kb/8OpgG.Ezf4DmwfP.QH0lvA15z6zXIY6sW', 'client', '2025-04-18 11:07:52', 'photo_68021c246dceb.jpg', NULL, NULL, NULL, NULL),
(104, 'livreur@livreur.com', 'livreur@livreur.com', '$2y$10$RBOhnJjblW9XxdR3IajW9eB5T9fArI/3q6BQyMSKhwb9Gp/kRyGG6', 'livreur', '2025-04-18 11:16:13', NULL, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
