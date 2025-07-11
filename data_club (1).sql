-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 11 juil. 2025 à 21:34
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

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

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`ID_ADMIN`, `FIRST_NAME`, `LAST_NAME`, `EMAIL`, `PASSWORD`, `PROFILE_IMG`) VALUES
(1, 'ttanani', 'mmouhsin', 'tanani@gmail.com', 'admin', NULL);

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
  `STATUS` varchar(20) DEFAULT NULL,
  `UNIVERSITY` varchar(50) DEFAULT NULL,
  `CITY` text NOT NULL,
  `INSTAGRAM_LINK` text NOT NULL,
  `LINKEDIN_LINK` text NOT NULL,
  `EMAIL` text NOT NULL,
  `CLUB_PHONE` int(11) NOT NULL,
  `Motif` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clubcreationrequest`
--

-- --------------------------------------------------------

--
-- Structure de la table `contains`
--

CREATE TABLE `contains` (
  `ID_EVENT` int(11) NOT NULL,
  `TOPIC_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contains`
--



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
  `CITY` text NOT NULL,
  `EVENT_TYPE` varchar(50) DEFAULT NULL,
  `PRICE` float DEFAULT NULL,
  `CAPACITY` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `evenement`


-- --------------------------------------------------------

--
-- Structure de la table `event_session`
--

CREATE TABLE `event_session` (
  `SESSION_ID` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL,
  `SESSION_NAME` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `event_session`
--


-- --------------------------------------------------------

--
-- Structure de la table `focuses`
--

CREATE TABLE `focuses` (
  `ID_CLUB` int(11) NOT NULL,
  `TOPIC_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `focuses`
--


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


--
-- Structure de la table `organizes`
--

CREATE TABLE `organizes` (
  `ID_CLUB` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `organizes`
--



-- --------------------------------------------------------

--
-- Structure de la table `registre`
--

CREATE TABLE `registre` (
  `ID_EVENT` int(11) NOT NULL,
  `ID_MEMBER` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `registre`
--

-- --------------------------------------------------------

--
-- Structure de la table `requestjoin`
--

CREATE TABLE `requestjoin` (
  `ID_MEMBER` int(11) NOT NULL,
  `ID_CLUB` int(11) NOT NULL,
  `ACCEPTED` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `requestjoin`

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

--
-- Déchargement des données de la table `speaker`
--

-- --------------------------------------------------------

--
-- Structure de la table `speaks`
--

CREATE TABLE `speaks` (
  `SPEAKER_ID` int(11) NOT NULL,
  `ID_EVENT` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--

-- --------------------------------------------------------

--
-- Structure de la table `topics`
--

CREATE TABLE `topics` (
  `TOPIC_ID` int(11) NOT NULL,
  `TOPIC_NAME` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `topics`
--



-- --------------------------------------------------------

--
-- Structure de la table `willfocus`
--

CREATE TABLE `willfocus` (
  `ID_REQUEST` int(11) NOT NULL,
  `TOPIC_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `willfocus`


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
  ADD PRIMARY KEY (`ID_CLUB`);

--
-- Index pour la table `clubcreationrequest`
--
ALTER TABLE `clubcreationrequest`
  ADD PRIMARY KEY (`ID_REQUEST`),
  ADD KEY `ID_MEMBER` (`ID_MEMBER`),
  ADD KEY `ID_ADMIN` (`ID_ADMIN`);

--
-- Index pour la table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`ID_EVENT`,`TOPIC_ID`),
  ADD KEY `TOPIC_ID` (`TOPIC_ID`);

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
  ADD KEY `ID_EVENT` (`ID_EVENT`);

--
-- Index pour la table `focuses`
--
ALTER TABLE `focuses`
  ADD PRIMARY KEY (`ID_CLUB`,`TOPIC_ID`),
  ADD KEY `TOPIC_ID` (`TOPIC_ID`);

--
-- Index pour la table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`ID_MEMBER`),
  ADD KEY `ID_CLUB` (`ID_CLUB`);

--
-- Index pour la table `organizes`
--
ALTER TABLE `organizes`
  ADD PRIMARY KEY (`ID_CLUB`,`ID_EVENT`),
  ADD KEY `ID_EVENT` (`ID_EVENT`);

--
-- Index pour la table `registre`
--
ALTER TABLE `registre`
  ADD PRIMARY KEY (`ID_EVENT`,`ID_MEMBER`),
  ADD KEY `ID_MEMBER` (`ID_MEMBER`);

--
-- Index pour la table `requestjoin`
--
ALTER TABLE `requestjoin`
  ADD PRIMARY KEY (`ID_MEMBER`,`ID_CLUB`),
  ADD KEY `ID_CLUB` (`ID_CLUB`);

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
  ADD KEY `ID_EVENT` (`ID_EVENT`);

--
-- Index pour la table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`TOPIC_ID`);

--
-- Index pour la table `willfocus`
--
ALTER TABLE `willfocus`
  ADD PRIMARY KEY (`ID_REQUEST`,`TOPIC_ID`),
  ADD KEY `ID_REQUEST` (`ID_REQUEST`),
  ADD KEY `ID_TOPIC` (`TOPIC_ID`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `ID_ADMIN` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `club`
--
ALTER TABLE `club`
  MODIFY `ID_CLUB` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `clubcreationrequest`
--
ALTER TABLE `clubcreationrequest`
  MODIFY `ID_REQUEST` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `ID_EVENT` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `event_session`
--
ALTER TABLE `event_session`
  MODIFY `SESSION_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `member`
--
ALTER TABLE `member`
  MODIFY `ID_MEMBER` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `speaker`
--
ALTER TABLE `speaker`
  MODIFY `SPEAKER_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `topics`
--
ALTER TABLE `topics`
  MODIFY `TOPIC_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `clubcreationrequest`
--
ALTER TABLE `clubcreationrequest`
  ADD CONSTRAINT `clubcreationrequest_ibfk_1` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`) ON DELETE CASCADE,
  ADD CONSTRAINT `clubcreationrequest_ibfk_2` FOREIGN KEY (`ID_ADMIN`) REFERENCES `admin` (`ID_ADMIN`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `contains_ibfk_1` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE,
  ADD CONSTRAINT `contains_ibfk_2` FOREIGN KEY (`TOPIC_ID`) REFERENCES `topics` (`TOPIC_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `event_session`
--
ALTER TABLE `event_session`
  ADD CONSTRAINT `event_session_ibfk_1` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `focuses`
--
ALTER TABLE `focuses`
  ADD CONSTRAINT `focuses_ibfk_1` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`) ON DELETE CASCADE,
  ADD CONSTRAINT `focuses_ibfk_2` FOREIGN KEY (`TOPIC_ID`) REFERENCES `topics` (`TOPIC_ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `member_ibfk_1` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`) ON DELETE CASCADE;

--
-- Contraintes pour la table `organizes`
--
ALTER TABLE `organizes`
  ADD CONSTRAINT `organizes_ibfk_1` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`) ON DELETE CASCADE,
  ADD CONSTRAINT `organizes_ibfk_2` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE;

--
-- Contraintes pour la table `registre`
--
ALTER TABLE `registre`
  ADD CONSTRAINT `registre_ibfk_1` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE,
  ADD CONSTRAINT `registre_ibfk_2` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`) ON DELETE CASCADE;

--
-- Contraintes pour la table `requestjoin`
--
ALTER TABLE `requestjoin`
  ADD CONSTRAINT `requestjoin_ibfk_1` FOREIGN KEY (`ID_MEMBER`) REFERENCES `member` (`ID_MEMBER`) ON DELETE CASCADE,
  ADD CONSTRAINT `requestjoin_ibfk_2` FOREIGN KEY (`ID_CLUB`) REFERENCES `club` (`ID_CLUB`) ON DELETE CASCADE;

--
-- Contraintes pour la table `speaks`
--
ALTER TABLE `speaks`
  ADD CONSTRAINT `speaks_ibfk_1` FOREIGN KEY (`SPEAKER_ID`) REFERENCES `speaker` (`SPEAKER_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `speaks_ibfk_2` FOREIGN KEY (`ID_EVENT`) REFERENCES `evenement` (`ID_EVENT`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
