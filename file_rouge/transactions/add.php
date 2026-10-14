<?php
// =====================================================================
// Fichier : transactions/add.php
// Description : Ajout d'une transaction et mise à jour automatique du solde
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

// 1. Récupération des comptes avec les noms des utilisateurs pour le <select>
$comptes_sql = "SELECT c.id_compte, c.nom_compte, c.solde, u.nom, u.prenom 
                FROM Compte_bancaire c
                INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
                ORDER BY u.nom, c.nom_compte";
$comptes = $pdo->query($comptes_sql)->fetchAll();

// 2. Récupération des catégories pour le <select>
$categories = $pdo->query("SELECT id_categorie, nom_categorie, type FROM Categorie_depense ORDER BY type, nom_categorie")->fetchAll();

$error_message    = '';
$id_compte        = '';
$id_categorie     = '';
$montant          = '';
$type_transaction = 'debit';
$description      = '';
$date_transaction = date('Y-m-d');

// 3. Traitement lors de la soumission du formulaire
if (isset($_POST['submit'])) {
    $id_compte        = (int)($_POST['id_compte'] ?? 0);
    $id_categorie     = (int)($_POST['id_categorie'] ?? 0);
    $montant          = trim($_POST['montant'] ?? '');
    $type_transaction = $_POST['type_transaction'] ?? 'debit';
    $description      = trim($_POST['description'] ?? '');
    $date_transaction = $_POST['date_transaction'] ?? '';

    $types_autorises = ['debit', 'credit'];

    if ($id_compte <= 0) {
        $error_message = "Veuillez sélectionner un compte bancaire.";
    } elseif ($id_categorie <= 0) {
        $error_message = "Veuillez sélectionner une catégorie.";
    } elseif (!is_numeric($montant) || (float)$montant <= 0) {
        $error_message = "Le montant doit être un nombre strictement supérieur à zéro.";
    } elseif (!in_array($type_transaction, $types_autorises)) {
        $error_message = "Type de transaction invalide.";
    } elseif (empty($date_transaction)) {
        $error_message = "La date de la transaction est obligatoire.";
    } else {
        $montant_float = (float)$montant;

        try {
            // Démarrage d'une transaction SQL pour garantir la cohérence des deux requêtes
            $pdo->beginTransaction();

            // A) Insertion de la nouvelle transaction
            $insert_sql = "INSERT INTO Transaction (id_compte, id_categorie, montant, type_transaction, description, date_transaction) 
                           VALUES (:id_compte, :id_categorie, :montant, :type_transaction, :description, :date_transaction)";
            $stmt = $pdo->prepare($insert_sql);
            $stmt->execute([
                ':id_compte'        => $id_compte,
                ':id_categorie'     => $id_categorie,
                ':montant'          => $montant_float,
                ':type_transaction' => $type_transaction,
                ':description'      => !empty($description) ? $description : null,
                ':date_transaction' => $date_transaction
            ]);

            // B) Mise à jour automatique du solde du compte bancaire :
            //    - Crédit : solde = solde + montant
            //    - Débit  : solde = solde - montant
            if ($type_transaction === 'credit') {
                $update_compte = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde + :montant WHERE id_compte = :id_compte");
            } else {
                $update_compte = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde - :montant WHERE id_compte = :id_compte");
            }

            $update_compte->execute([
                ':montant'   => $montant_float,
                ':id_compte' => $id_compte
            ]);

            // Validation finale des deux opérations
            $pdo->commit();

            $_SESSION['success'] = "La transaction de " . number_format($montant_float, 2, ',', ' ') . " € a été enregistrée et le solde du compte a été mis à jour.";
            header('Location: list.php');
            exit;

        } catch (PDOException $e) {
            // En cas d'échec, annuler toutes les modifications
            $pdo->rollBack();
            $error_message = "Erreur lors de l'enregistrement de la transaction : " . $e->getMessage();
        }
    }
}

$page_title = "Nouvelle Transaction";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>➕ Nouvelle Transaction</h1>
        <p>Enregistrer une recette ou une dépense et actualiser le solde</p>
    </div>
    <a href="list.php" class="btn btn-secondary">⬅️ Retour à la liste</a>
</div>

<div class="card form-card">
    <form action="add.php" method="POST">
        <div class="form-group">
            <label for="id_compte">Compte bancaire concerné <span class="required">*</span></label>
            <select name="id_compte" id="id_compte" class="form-control" required>
                <option value="">-- Sélectionnez un compte --</option>
                <?php foreach ($comptes as $c): ?>
                    <option value="<?= $c['id_compte'] ?>" <?= ($id_compte == $c['id_compte']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nom_compte']) ?> (<?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>) - Solde : <?= number_format($c['solde'], 2, ',', ' ') ?> €
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="id_categorie">Catégorie <span class="required">*</span></label>
                <select name="id_categorie" id="id_categorie" class="form-control" required>
                    <option value="">-- Sélectionnez une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id_categorie'] ?>" <?= ($id_categorie == $cat['id_categorie']) ? 'selected' : '' ?>>
                            [<?= ucfirst($cat['type']) ?>] <?= htmlspecialchars($cat['nom_categorie']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="type_transaction">Type de mouvement <span class="required">*</span></label>
                <select name="type_transaction" id="type_transaction" class="form-control" required>
                    <option value="debit"  <?= ($type_transaction === 'debit') ? 'selected' : '' ?>>Débit (-) Dépense</option>
                    <option value="credit" <?= ($type_transaction === 'credit') ? 'selected' : '' ?>>Crédit (+) Entrée d'argent</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="montant">Montant (€) <span class="required">*</span></label>
                <input type="number" step="0.01" min="0.01" id="montant" name="montant" class="form-control" 
                       value="<?= htmlspecialchars($montant) ?>" required placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="date_transaction">Date de la transaction <span class="required">*</span></label>
                <input type="date" id="date_transaction" name="date_transaction" class="form-control" 
                       value="<?= htmlspecialchars($date_transaction) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description / Libellé (optionnel)</label>
            <input type="text" id="description" name="description" class="form-control" 
                   value="<?= htmlspecialchars($description) ?>" placeholder="Ex: Courses Carrefour, Salaire de base...">
        </div>

        <div class="form-actions">
            <a href="list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" name="submit" class="btn btn-primary">Valider la transaction</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
