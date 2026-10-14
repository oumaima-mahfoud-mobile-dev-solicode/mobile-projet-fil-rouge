<?php
// =====================================================================
// Fichier : transactions/edit.php
// Description : Modification d'une transaction et réajustement du solde
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

// 1. Récupération de l'ID depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Identifiant de transaction invalide.";
    header('Location: list.php');
    exit;
}

// 2. Recherche de la transaction existante
$stmt = $pdo->prepare("SELECT * FROM Transaction WHERE id_transaction = :id");
$stmt->execute([':id' => $id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    $_SESSION['error'] = "Transaction introuvable.";
    header('Location: list.php');
    exit;
}

// 3. Récupérer les listes pour les menus déroulants <select>
$comptes = $pdo->query("SELECT c.id_compte, c.nom_compte, c.solde, u.nom, u.prenom 
                        FROM Compte_bancaire c
                        INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
                        ORDER BY u.nom, c.nom_compte")->fetchAll();

$categories = $pdo->query("SELECT id_categorie, nom_categorie, type 
                           FROM Categorie_depense 
                           ORDER BY type, nom_categorie")->fetchAll();

$error_message    = '';
$id_compte        = $transaction['id_compte'];
$id_categorie     = $transaction['id_categorie'];
$montant          = $transaction['montant'];
$type_transaction = $transaction['type_transaction'];
$description      = $transaction['description'];
$date_transaction = $transaction['date_transaction'];

// 4. Traitement de la mise à jour lors de la soumission
if (isset($_POST['submit'])) {
    $nouveau_compte_id  = (int)($_POST['id_compte'] ?? 0);
    $nouvelle_cat_id    = (int)($_POST['id_categorie'] ?? 0);
    $nouveau_montant    = trim($_POST['montant'] ?? '');
    $nouveau_type       = $_POST['type_transaction'] ?? 'debit';
    $nouvelle_desc      = trim($_POST['description'] ?? '');
    $nouvelle_date      = $_POST['date_transaction'] ?? '';

    $types_autorises = ['debit', 'credit'];

    if ($nouveau_compte_id <= 0) {
        $error_message = "Veuillez sélectionner un compte bancaire.";
    } elseif ($nouvelle_cat_id <= 0) {
        $error_message = "Veuillez sélectionner une catégorie.";
    } elseif (!is_numeric($nouveau_montant) || (float)$nouveau_montant <= 0) {
        $error_message = "Le montant doit être supérieur à zéro.";
    } elseif (!in_array($nouveau_type, $types_autorises)) {
        $error_message = "Type de transaction invalide.";
    } elseif (empty($nouvelle_date)) {
        $error_message = "La date est obligatoire.";
    } else {
        $nouveau_montant_float = (float)$nouveau_montant;
        $ancien_montant_float  = (float)$transaction['montant'];
        $ancien_type           = $transaction['type_transaction'];
        $ancien_compte_id      = (int)$transaction['id_compte'];

        try {
            $pdo->beginTransaction();

            // Étape A : Annuler l'ancien impact financier sur l'ancien compte
            // Si c'était un crédit (+), on soustrait l'ancien montant
            // Si c'était un débit (-), on réintègre l'ancien montant
            if ($ancien_type === 'credit') {
                $cancel_stmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde - :ancien_montant WHERE id_compte = :ancien_id");
            } else {
                $cancel_stmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde + :ancien_montant WHERE id_compte = :ancien_id");
            }
            $cancel_stmt->execute([
                ':ancien_montant' => $ancien_montant_float,
                ':ancien_id'      => $ancien_compte_id
            ]);

            // Étape B : Appliquer le nouvel impact financier sur le (nouveau ou même) compte
            if ($nouveau_type === 'credit') {
                $apply_stmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde + :nouveau_montant WHERE id_compte = :nouveau_id");
            } else {
                $apply_stmt = $pdo->prepare("UPDATE Compte_bancaire SET solde = solde - :nouveau_montant WHERE id_compte = :nouveau_id");
            }
            $apply_stmt->execute([
                ':nouveau_montant' => $nouveau_montant_float,
                ':nouveau_id'      => $nouveau_compte_id
            ]);

            // Étape C : Mettre à jour les données de la transaction
            $update_sql = "UPDATE Transaction 
                           SET id_compte        = :id_compte, 
                               id_categorie     = :id_categorie, 
                               montant          = :montant, 
                               type_transaction = :type_transaction, 
                               description      = :description, 
                               date_transaction = :date_transaction 
                           WHERE id_transaction = :id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                ':id_compte'        => $nouveau_compte_id,
                ':id_categorie'     => $nouvelle_cat_id,
                ':montant'          => $nouveau_montant_float,
                ':type_transaction' => $nouveau_type,
                ':description'      => !empty($nouvelle_desc) ? $nouvelle_desc : null,
                ':date_transaction' => $nouvelle_date,
                ':id'               => $id
            ]);

            $pdo->commit();

            $_SESSION['success'] = "La transaction a été modifiée et le solde du compte a été réajusté avec succès.";
            header('Location: list.php');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}

$page_title = "Modifier la Transaction";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>✏️ Modifier la Transaction</h1>
        <p>Modification de la transaction #<?= htmlspecialchars($id) ?></p>
    </div>
    <a href="list.php" class="btn btn-secondary">⬅️ Retour à la liste</a>
</div>

<div class="card form-card">
    <form action="edit.php?id=<?= $id ?>" method="POST">
        <div class="form-group">
            <label for="id_compte">Compte bancaire concerné <span class="required">*</span></label>
            <select name="id_compte" id="id_compte" class="form-control" required>
                <option value="">-- Sélectionnez un compte --</option>
                <?php foreach ($comptes as $c): ?>
                    <option value="<?= $c['id_compte'] ?>" <?= ($id_compte == $c['id_compte']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nom_compte']) ?> (<?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>)
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
                       value="<?= htmlspecialchars($montant) ?>" required>
            </div>

            <div class="form-group">
                <label for="date_transaction">Date de la transaction <span class="required">*</span></label>
                <input type="date" id="date_transaction" name="date_transaction" class="form-control" 
                       value="<?= htmlspecialchars($date_transaction) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description / Libellé</label>
            <input type="text" id="description" name="description" class="form-control" 
                   value="<?= htmlspecialchars($description ?? '') ?>">
        </div>

        <div class="form-actions">
            <a href="list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" name="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
