<?php
// =====================================================================
// Fichier : transactions/delete.php
// Description : Suppression d'une transaction et annulation de son effet sur le solde
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $pdo->beginTransaction();

        // 1. Récupérer la transaction pour connaître son montant, son compte et son type
        $stmt = $pdo->prepare("SELECT * FROM Transaction WHERE id_transaction = :id");
        $stmt->execute([':id' => $id]);
        $transaction = $stmt->fetch();

        if ($transaction) {
            $id_compte = (int)$transaction['id_compte'];
            $montant   = (float)$transaction['montant'];
            $type      = $transaction['type_transaction'];

            // 2. Annuler l'impact sur le solde du compte (opération inverse) :
            //    - Si c'était un crédit : on le retire du solde (solde - montant)
            //    - Si c'était un débit  : on le réinjecte dans le solde (solde + montant)
            if ($type === 'credit') {
                $revert_stmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde - :montant WHERE id_compte = :id_compte");
            } else {
                $revert_stmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde + :montant WHERE id_compte = :id_compte");
            }

            $revert_stmt->execute([
                ':montant'   => $montant,
                ':id_compte' => $id_compte
            ]);

            // 3. Supprimer la transaction
            $del_stmt = $pdo->prepare("DELETE FROM Transaction WHERE id_transaction = :id");
            $del_stmt->execute([':id' => $id]);

            $pdo->commit();

            $_SESSION['success'] = "La transaction a été supprimée et le solde du compte a été rétabli.";
        } else {
            $pdo->rollBack();
            $_SESSION['error'] = "Transaction introuvable.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de la suppression de la transaction : " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Identifiant de transaction invalide.";
}

header('Location: list.php');
exit;
