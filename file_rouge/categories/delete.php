<?php
// =====================================================================
// Fichier : categories/delete.php
// Description : Suppression d'une catégorie avec interception de la contrainte RESTRICT
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Tentative de suppression de la catégorie
        $stmt = $pdo->prepare("DELETE FROM Categorie_depense WHERE id_categorie = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "La catégorie a été supprimée avec succès.";
    } catch (PDOException $e) {
        // En cas de contrainte RESTRICT (erreur 23000 / 1451) : la catégorie est rattachée à des transactions
        if ($e->getCode() === '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)) {
            $_SESSION['error'] = "Impossible de supprimer cette catégorie car elle est utilisée par une ou plusieurs transactions existantes.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
} else {
    $_SESSION['error'] = "Identifiant de catégorie invalide.";
}

header('Location: list.php');
exit;
