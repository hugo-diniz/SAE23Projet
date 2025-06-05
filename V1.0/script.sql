-- Création de la base
CREATE DATABASE Mesures;
USE Mesures;

-- Table Batiment
CREATE TABLE Batiment (
    IdBat INT AUTO_INCREMENT PRIMARY KEY,
    NomBat VARCHAR(100) NOT NULL,
    NomGest VARCHAR(100),
    MDPGest VARCHAR(255)
);

-- Table Salle
CREATE TABLE Salle (
    NomSalle VARCHAR(100) PRIMARY KEY,
    Type VARCHAR(50),
    Capacite INT,
    IdBat INT,
    FOREIGN KEY (IdBat) REFERENCES Batiment(IdBat)
);

-- Table Capteur
CREATE TABLE Capteur (
    NomCap VARCHAR(100) PRIMARY KEY,
    Type VARCHAR(50),
    Unite VARCHAR(50),
    NomSalle VARCHAR(100),
    FOREIGN KEY (NomSalle) REFERENCES Salle(NomSalle)
);

-- Table Mesure
CREATE TABLE Mesure (
    IdMes INT AUTO_INCREMENT PRIMARY KEY,
    Date DATE,
    Heure TIME,
    Valeur FLOAT,
    NomCap VARCHAR(100),
    FOREIGN KEY (NomCap) REFERENCES Capteur(NomCap)
);

-- Table Admin
CREATE TABLE Admin (
    NomAdmin VARCHAR(100) PRIMARY KEY,
    MdpAdmin VARCHAR(255)
);
