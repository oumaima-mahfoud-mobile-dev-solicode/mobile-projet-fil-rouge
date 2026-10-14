-- =====================================================================
-- Base de données : file_rouge_db
-- Application de gestion de finances personnelles (Partie 1)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS file_rouge_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE file_rouge_db;

-- ---------------------------------------------------------------------
-- Table : Utilisateur
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table : Compte_bancaire
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Compte_bancaire (
    id_compte INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    nom_compte VARCHAR(100) NOT NULL,
    type_compte ENUM('courant', 'epargne', 'autre') NOT NULL,
    solde DECIMAL(10,2) DEFAULT 0.00,
    date_ouverture DATE NOT NULL,
    CONSTRAINT fk_compte_utilisateur
        FOREIGN KEY (id_utilisateur)
        REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table : Categorie_depense
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Categorie_depense (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL,
    type ENUM('depense', 'revenu') NOT NULL,
    icone VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table : Transaction
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Transaction (
    id_transaction INT AUTO_INCREMENT PRIMARY KEY,
    id_compte INT NOT NULL,
    id_categorie INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    type_transaction ENUM('debit', 'credit') NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    date_transaction DATE NOT NULL,
    CONSTRAINT fk_transaction_compte
        FOREIGN KEY (id_compte)
        REFERENCES Compte_bancaire(id_compte)
        ON DELETE CASCADE,
    CONSTRAINT fk_transaction_categorie
        FOREIGN KEY (id_categorie)
        REFERENCES Categorie_depense(id_categorie)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
