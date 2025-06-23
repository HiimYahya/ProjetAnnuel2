-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 23 juin 2025 à 19:28
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
  `segmentation_possible` tinyint(1) NOT NULL DEFAULT '1',
  `ville_depart` varchar(255) NOT NULL,
  `ville_arrivee` varchar(255) NOT NULL,
  `description` text,
  `prix` decimal(10,2) DEFAULT NULL,
  `validation_client` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = validé par le client, 0 = en attente de validation',
  `hauteur` float DEFAULT NULL,
  `longueur` float DEFAULT NULL,
  `largeur` float DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `code_validation` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_client` (`id_client`),
  KEY `id_livreur` (`id_livreur`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `livraisons`
--

INSERT INTO `livraisons` (`id`, `id_client`, `id_livreur`, `id_annonce`, `date_prise_en_charge`, `date_livraison`, `statut`, `segmentation_possible`, `ville_depart`, `ville_arrivee`, `description`, `prix`, `validation_client`, `hauteur`, `longueur`, `largeur`, `titre`, `code_validation`) VALUES
(72, 103, 104, 83, '2025-06-23 21:27:52', NULL, 'en cours', 1, '25 Grand Rue Jean Moulin, 34000 Montpellier', '88 rue de Raymond Poincaré, 11100 Narbonne', '10', 10.00, 1, 10, 10, 10, 'HULK', '754165'),
(71, 103, 104, 82, '2025-06-23 21:27:51', NULL, 'en cours', 1, '7 allée soufflot, 92600 Asnières-sur-Seine', '25 Grand Rue Jean Moulin, 34000 Montpellier', '10', 10.00, 1, 10, 10, 10, 'HULK', NULL),
(69, 103, 104, 80, '2025-06-23 21:16:41', '2025-06-23 21:27:19', 'livrée', 1, '7 allée soufflot, 92600 Asnières-sur-Seine', '54 Av. Jean Médecin, 06000 Nice', '10', 10.00, 1, 10, 10, 10, 'RONALDO', NULL),
(70, 103, 104, 81, '2025-06-23 21:16:43', '2025-06-23 21:27:14', 'livrée', 1, '54 Av. Jean Médecin, 06000 Nice', '88 rue de Raymond Poincaré, 11100 Narbonne', '10', 10.00, 1, 10, 10, 10, 'RONALDO', '460652');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
