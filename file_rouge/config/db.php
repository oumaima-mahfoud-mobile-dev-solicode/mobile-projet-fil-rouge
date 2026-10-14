<?php
// =====================================================================
// Fichier : config/db.php
// Description : Connexion simple à la base de données MySQL via PDO
// =====================================================================

// Paramètres de configuration de la base de données
$host     = '127.0.0.1';
$port     = '3306';
$dbname   = 'file_rouge_db';
$username = 'root';
$password = 'Solicode@2';
$charset  = 'utf8mb4';

// Chaîne de connexion DSN (Data Source Name)
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

try {
    // Création de l'objet PDO pour communiquer avec MySQL
    $pdo = new PDO($dsn, $username, $password, [
        // Déclencher une exception en cas d'erreur SQL
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Récupérer les résultats sous forme de tableau associatif par défaut
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Désactiver l'émulation des requêtes préparées pour une sécurité accrue
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, on affiche un message clair et on arrête le script
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
