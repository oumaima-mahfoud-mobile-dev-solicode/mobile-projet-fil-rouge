-- =====================================================================
-- Données de test : seed.sql
-- Mot de passe pour tous les utilisateurs : Password123!
-- Haché avec password_hash('Password123!', PASSWORD_BCRYPT)
-- =====================================================================

USE file_rouge_db;

-- Désactiver temporairement les vérifications de clés étrangères pour réinitialiser proprement
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Transaction;
TRUNCATE TABLE Categorie_depense;
TRUNCATE TABLE Compte_bancaire;
TRUNCATE TABLE Utilisateur;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. Insertion de 3 utilisateurs
-- ---------------------------------------------------------------------
INSERT INTO Utilisateur (id_utilisateur, nom, prenom, email, mot_de_passe, date_creation) VALUES
(1, 'Dupont', 'Alice', 'alice.dupont@example.com', '$2y$10$gkn4mOCgJjSEv0HHibilTOP1rofhP9NPB/D3lUoAcvUN.mO9lhqAq', '2026-01-01 10:00:00'),
(2, 'Martin', 'Bob', 'bob.martin@example.com', '$2y$10$gkn4mOCgJjSEv0HHibilTOP1rofhP9NPB/D3lUoAcvUN.mO9lhqAq', '2026-01-02 11:30:00'),
(3, 'Bernard', 'Clara', 'clara.bernard@example.com', '$2y$10$gkn4mOCgJjSEv0HHibilTOP1rofhP9NPB/D3lUoAcvUN.mO9lhqAq', '2026-01-03 09:15:00');

-- ---------------------------------------------------------------------
-- 2. Insertion de 6 comptes bancaires (2 par utilisateur)
-- ---------------------------------------------------------------------
INSERT INTO Compte_bancaire (id_compte, id_utilisateur, nom_compte, type_compte, solde, date_ouverture) VALUES
(1, 1, 'Compte Courant Alice', 'courant', 1584.50, '2026-01-01'),
(2, 1, 'Livret Epargne Alice', 'epargne', 350.00, '2026-01-01'),
(3, 2, 'Compte Courant Bob', 'courant', 1825.00, '2026-01-02'),
(4, 2, 'Epargne Projet Bob', 'epargne', 500.00, '2026-01-02'),
(5, 3, 'Compte Principal Clara', 'courant', 1384.80, '2026-01-03'),
(6, 3, 'Compte Vacances Clara', 'autre', 280.00, '2026-01-03');

-- ---------------------------------------------------------------------
-- 3. Insertion de 6 catégories
-- ---------------------------------------------------------------------
INSERT INTO Categorie_depense (id_categorie, nom_categorie, type, icone) VALUES
(1, 'Alimentation', 'depense', 'utensils'),
(2, 'Transport', 'depense', 'car'),
(3, 'Loisirs', 'depense', 'gamepad'),
(4, 'Loyer', 'depense', 'home'),
(5, 'Santé', 'depense', 'heartbeat'),
(6, 'Salaire', 'revenu', 'money-bill-wave');

-- ---------------------------------------------------------------------
-- 4. Insertion de 18 transactions (soldes strictement cohérents)
-- ---------------------------------------------------------------------
-- Compte 1 (Alice - Courant) : 2500.00 (crédit) - 750.00 - 120.50 - 45.00 = 1584.50
INSERT INTO Transaction (id_transaction, id_compte, id_categorie, montant, type_transaction, description, date_transaction) VALUES
(1, 1, 6, 2500.00, 'credit', 'Virement salaire mensuel', '2026-01-05'),
(2, 1, 4, 750.00, 'debit', 'Loyer appartement janvier', '2026-01-06'),
(3, 1, 1, 120.50, 'debit', 'Courses supermarché', '2026-01-10'),
(4, 1, 2, 45.00, 'debit', 'Abonnement transport en commun', '2026-01-15');

-- Compte 2 (Alice - Épargne) : 500.00 (crédit) - 150.00 = 350.00
INSERT INTO Transaction (id_transaction, id_compte, id_categorie, montant, type_transaction, description, date_transaction) VALUES
(5, 2, 6, 500.00, 'credit', 'Virement épargne précaution', '2026-01-07'),
(6, 2, 3, 150.00, 'debit', 'Achat matériel de loisir', '2026-01-20');

-- Compte 3 (Bob - Courant) : 3000.00 (crédit) - 900.00 - 210.00 - 65.00 = 1825.00
INSERT INTO Transaction (id_transaction, id_compte, id_categorie, montant, type_transaction, description, date_transaction) VALUES
(7, 3, 6, 3000.00, 'credit', 'Salaire mensuel Bob', '2026-01-05'),
(8, 3, 4, 900.00, 'debit', 'Loyer maison janvier', '2026-01-06'),
(9, 3, 1, 210.00, 'debit', 'Courses marché et épicerie', '2026-01-12'),
(10, 3, 5, 65.00, 'debit', 'Pharmacie et soins santé', '2026-01-18');

-- Compte 4 (Bob - Épargne) : 800.00 (crédit) - 300.00 = 500.00
INSERT INTO Transaction (id_transaction, id_compte, id_categorie, montant, type_transaction, description, date_transaction) VALUES
(11, 4, 6, 800.00, 'credit', 'Dépôt épargne projet', '2026-01-08'),
(12, 4, 3, 300.00, 'debit', 'Réservation week-end détente', '2026-01-25');

-- Compte 5 (Clara - Courant) : 2200.00 (crédit) - 600.00 - 180.20 - 35.00 = 1384.80
INSERT INTO Transaction (id_transaction, id_compte, id_categorie, montant, type_transaction, description, date_transaction) VALUES
(13, 5, 6, 2200.00, 'credit', 'Salaire Clara janvier', '2026-01-05'),
(14, 5, 4, 600.00, 'debit', 'Loyer studio', '2026-01-06'),
(15, 5, 1, 180.20, 'debit', 'Panier bio et supermarché', '2026-01-14'),
(16, 5, 2, 35.00, 'debit', 'Carburant station service', '2026-01-19');

-- Compte 6 (Clara - Vacances) : 400.00 (crédit) - 120.00 = 280.00
INSERT INTO Transaction (id_transaction, id_compte, id_categorie, montant, type_transaction, description, date_transaction) VALUES
(17, 6, 6, 400.00, 'credit', 'Versement cagnotte vacances', '2026-01-10'),
(18, 6, 3, 120.00, 'debit', 'Billets parc d attractions', '2026-01-22');
