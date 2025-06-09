-- DataBase creation
CREATE DATABASE Mesures;
USE Mesures;

-- Batiment Table
CREATE TABLE Batiment (
    IdBat INT AUTO_INCREMENT PRIMARY KEY,
    NomBat VARCHAR(100) NOT NULL,
    NomGest VARCHAR(100),
    MDPGest VARCHAR(255)
);

--  Salle Table
CREATE TABLE Salle (
    NomSalle VARCHAR(100) PRIMARY KEY,
    Type VARCHAR(50),
    Capacite INT,
    IdBat INT,
    FOREIGN KEY (IdBat) REFERENCES Batiment(IdBat)
);

-- visible_tables Table
CREATE TABLE IF NOT EXISTS visible_tables (
  table_name VARCHAR(100) PRIMARY KEY
);


-- Capteur Table
CREATE TABLE Capteur (
    NomCap VARCHAR(100) PRIMARY KEY,
    Type VARCHAR(50),
    Unite VARCHAR(50),
    NomSalle VARCHAR(100),
    FOREIGN KEY (NomSalle) REFERENCES Salle(NomSalle)
);

-- Mesure Table
CREATE TABLE Mesure (
    IdMes INT AUTO_INCREMENT PRIMARY KEY,
    Date DATE,
    Heure TIME,
    Valeur FLOAT,
    NomCap VARCHAR(100),
    FOREIGN KEY (NomCap) REFERENCES Capteur(NomCap)
);

-- Admin Table
CREATE TABLE Admin (
    NomAdmin VARCHAR(100) PRIMARY KEY,
    MdpAdmin VARCHAR(255)
);
