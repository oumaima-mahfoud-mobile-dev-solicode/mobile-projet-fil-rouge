<?php
// =====================================================================
// Fichier : comptes/delete.php
// Description : Suppression d'un compte bancaire
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // En supprimant le compte, ses transactions associées sont supprimées en cascade
        $stmt = $pdo->prepare("DELETE FROM Compte_bancaire WHERE id_compte = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "Le compte bancaire a été supprimé avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Identifiant de compte invalide.";
}

header('Location: list.php');
exit;
