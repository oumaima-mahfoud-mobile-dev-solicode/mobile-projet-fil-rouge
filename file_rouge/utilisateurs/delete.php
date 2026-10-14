<?php
// =====================================================================
// Fichier : utilisateurs/delete.php
// Description : Suppression d'un utilisateur
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

// 1. Récupération de l'identifiant depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Grâce à ON DELETE CASCADE défini dans schema.sql,
        // la suppression de l'utilisateur supprime automatiquement ses comptes et transactions
        $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE id_utilisateur = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "L'utilisateur a été supprimé avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Identifiant d'utilisateur invalide.";
}

// 2. Redirection vers la liste des utilisateurs
header('Location: list.php');
exit;
