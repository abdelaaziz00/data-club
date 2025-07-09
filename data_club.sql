-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 06 juil. 2025 à 20:39
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `data_club`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `ID_ADMIN` int(11) NOT NULL,
  `FIRST_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `PASSWORD` varchar(50) DEFAULT NULL,
  `PROFILE_IMG` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `club`
--

CREATE TABLE `club` (
  `ID_CLUB` int(11) NOT NULL,
  `ID_MEMBER` int(11) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL,
  `LOGO` varchar(80) DEFAULT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `UNIVERSITY` varchar(50) DEFAULT NULL,
  `CITY` char(10) DEFAULT NULL,
  `INSTAGRAM_LINK` char(10) DEFAULT NULL,
  `LINKEDIN_LINK` char(10) DEFAULT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `CLUB_PHONE` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `clubcreationrequest`
--

CREATE TABLE `clubcreationrequest` (
  `ID_REQUEST` int(11) NOT NULL,
  `ID_MEMBER` int(11) NOT NULL,
  `ID_ADMIN` int(11) DEFAULT NULL,
  `CLUB_NAME` varchar(50) DEFAULT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `TEAM_MEMBERS` int(11) DEFAULT NULL,
  `SOCIAL_LINKS` varchar(70) DEFAULT NULL,
  `STATUS` varchar(20) DEFAULT NULL,
  `UNIVERSITY` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contains`
--

CREATE TABLE `contains` (
  `ID_EVENT` int(11) NOT NULL,
  `TOPIC_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `ID_EVENT` int(11) NOT NULL,
  `TITLE` varchar(70) DEFAULT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `DATE` date DEFAULT NULL,
  `STARTING_TIME` time DEFAULT NULL,
  `ENDING_TIME` time DEFAULT NULL,
  `LOCATION` varchar(100) DEFAULT NULL,
  `EVENT_TYPE` varchar(50) DEFAULT NULL,
  `PRICE` float DEFAULT NULL,
  `CAPACITY` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `event_session`
--

CREATE TABLE `event_session` (
  `SESSION_ID` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL,
  `SESSION_NAME` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `focuses`
--

CREATE TABLE `focuses` (
  `ID_CLUB` int(11) NOT NULL,
  `TOPIC_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `member`
--

CREATE TABLE `member` (
  `ID_MEMBER` int(11) NOT NULL,
  `ID_CLUB` int(11) DEFAULT NULL,
  `PROFILE_IMG` varchar(80) DEFAULT NULL,
  `FIRST_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `PASSWORD` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `member`
--

INSERT INTO `member` (`ID_MEMBER`, `ID_CLUB`, `PROFILE_IMG`, `FIRST_NAME`, `LAST_NAME`, `EMAIL`, `PASSWORD`) VALUES
(0, NULL, NULL, 'abdelaaziz', 'belharcha', 'abdelaaziz.belharcha@gmail.com', 'aaaaaa');

-- --------------------------------------------------------

--
-- Structure de la table `organizes`
--

CREATE TABLE `organizes` (
  `ID_CLUB` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `registre`
--

CREATE TABLE `registre` (
  `ID_EVENT` int(11) NOT NULL,
  `ID_MEMBER` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `requestjoin`
--

CREATE TABLE `requestjoin` (
  `ID_MEMBER` int(11) NOT NULL,
  `ID_CLUB` int(11) NOT NULL,
  `ACCEPTED` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `speaker`
--

CREATE TABLE `speaker` (
  `SPEAKER_ID` int(11) NOT NULL,
  `SPEAKER_FULLNAME` text DEFAULT NULL,
  `SPEAKER_DISCRIPTION` text DEFAULT NULL,
  `SPEAKER_COMPANY` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `speaks`
--

CREATE TABLE `speaks` (
  `SPEAKER_ID` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `topics`
--

CREATE TABLE `topics` (
  `TOPIC_ID` int(11) NOT NULL,
  `TOPIC_NAME` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ID_ADMIN`);

--
-- Index pour la table `club`
--
ALTER TABLE `club`
  ADD PRIMARY KEY (`ID_CLUB`),
  ADD KEY `FK_CREATE` (`ID_MEMBER`);

--
-- Index pour la table `clubcreationrequest`
--
ALTER TABLE `clubcreationrequest`
  ADD PRIMARY KEY (`ID_REQUEST`),
  ADD KEY `FK_ACCEPT` (`ID_ADMIN`),
  ADD KEY `FK_CREATESCLUBREQUEST` (`ID_MEMBER`);

--
-- Index pour la table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`ID_EVENT`,`TOPIC_ID`),
  ADD KEY `FK_CONTAINS2` (`TOPIC_ID`);

--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`ID_EVENT`);

--
-- Index pour la table `event_session`
--
ALTER TABLE `event_session`
  ADD PRIMARY KEY (`SESSION_ID`),
  ADD KEY `FK_HAS` (`ID_EVENT`);

--
-- Index pour la table `focuses`
--
ALTER TABLE `focuses`
  ADD PRIMARY KEY (`ID_CLUB`,`TOPIC_ID`),
  ADD KEY `FK_FOCUSES2` (`TOPIC_ID`);

--
-- Index pour la table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`ID_MEMBER`),
  ADD KEY `FK_CREATE2` (`ID_CLUB`);

--
-- Index pour la table `organizes`
--
ALTER TABLE `organizes`
  ADD PRIMARY KEY (`ID_CLUB`,`ID_EVENT`),
  ADD KEY `FK_ORGANIZES2` (`ID_EVENT`);

--
-- Index pour la table `registre`
--
ALTER TABLE `registre`
  ADD PRIMARY KEY (`ID_EVENT`,`ID_MEMBER`),
  ADD KEY `FK_REGISTRE2` (`ID_MEMBER`);

--
-- Index pour la table `requestjoin`
--
ALTER TABLE `requestjoin`
  ADD PRIMARY KEY (`ID_MEMBER`,`ID_CLUB`),
  ADD KEY `FK_REQUESTJOIN2` (`ID_CLUB`);

--
-- Index pour la table `speaker`
--
ALTER TABLE `speaker`
  ADD PRIMARY KEY (`SPEAKER_ID`);

--
-- Index pour la table `speaks`
--
ALTER TABLE `speaks`
  ADD PRIMARY KEY (`SPEAKER_ID`,`ID_EVENT`),
  ADD KEY `FK_SPEAKS2` (`ID_EVENT`);

--
-- Index pour la table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`TOPIC_ID`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `club`
--
ALTER TABLE `club`
  ADD CONSTRAINT `FK_CREATE` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`);

--
-- Contraintes pour la table `clubcreationrequest`
--
ALTER TABLE `clubcreationrequest`
  ADD CONSTRAINT `FK_ACCEPT` FOREIGN KEY (`ID_ADMIN`) REFERENCES `admin` (`ID_ADMIN`),
  ADD CONSTRAINT `FK_CREATESCLUBREQUEST` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`);

--
-- Contraintes pour la table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `FK_CONTAINS` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`),
  ADD CONSTRAINT `FK_CONTAINS2` FOREIGN KEY (`TOPIC_ID`) REFERENCES `topics` (`TOPIC_ID`);

--
-- Contraintes pour la table `event_session`
--
ALTER TABLE `event_session`
  ADD CONSTRAINT `FK_HAS` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`);

--
-- Contraintes pour la table `focuses`
--
ALTER TABLE `focuses`
  ADD CONSTRAINT `FK_FOCUSES` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`),
  ADD CONSTRAINT `FK_FOCUSES2` FOREIGN KEY (`TOPIC_ID`) REFERENCES `topics` (`TOPIC_ID`);

--
-- Contraintes pour la table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `FK_CREATE2` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`);

--
-- Contraintes pour la table `organizes`
--
ALTER TABLE `organizes`
  ADD CONSTRAINT `FK_ORGANIZES` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`),
  ADD CONSTRAINT `FK_ORGANIZES2` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`);

--
-- Contraintes pour la table `registre`
--
ALTER TABLE `registre`
  ADD CONSTRAINT `FK_REGISTRE` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`),
  ADD CONSTRAINT `FK_REGISTRE2` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`);

--
-- Contraintes pour la table `requestjoin`
--
ALTER TABLE `requestjoin`
  ADD CONSTRAINT `FK_REQUESTJOIN` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`),
  ADD CONSTRAINT `FK_REQUESTJOIN2` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`);

--
-- Contraintes pour la table `speaks`
--
ALTER TABLE `speaks`
  ADD CONSTRAINT `FK_SPEAKS` FOREIGN KEY (`SPEAKER_ID`) REFERENCES `speaker` (`SPEAKER_ID`),
  ADD CONSTRAINT `FK_SPEAKS2` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
