<?php
// =====================================================================
// Fichier : comptes/edit.php
// Description : Modification d'un compte bancaire
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

// 1. Récupération de l'ID du compte depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Identifiant de compte invalide.";
    header('Location: list.php');
    exit;
}

// 2. Recherche du compte
$stmt = $pdo->prepare("SELECT * FROM Compte_bancaire WHERE id_compte = :id");
$stmt->execute([':id' => $id]);
$compte = $stmt->fetch();

if (!$compte) {
    $_SESSION['error'] = "Compte bancaire introuvable.";
    header('Location: list.php');
    exit;
}

// 3. Récupérer la liste des utilisateurs pour le <select>
$users_stmt = $pdo->query("SELECT id_utilisateur, nom, prenom FROM Utilisateur ORDER BY prenom, nom ASC");
$utilisateurs = $users_stmt->fetchAll();

$error_message  = '';
$id_utilisateur = $compte['id_utilisateur'];
$nom_compte     = $compte['nom_compte'];
$type_compte    = $compte['type_compte'];
$solde          = $compte['solde'];
$date_ouverture = $compte['date_ouverture'];

// 4. Traitement de la mise à jour
if (isset($_POST['submit'])) {
    $id_utilisateur = (int)($_POST['id_utilisateur'] ?? 0);
    $nom_compte     = trim($_POST['nom_compte'] ?? '');
    $type_compte    = $_POST['type_compte'] ?? 'courant';
    $solde          = trim($_POST['solde'] ?? '0.00');
    $date_ouverture = $_POST['date_ouverture'] ?? '';

    $types_autorises = ['courant', 'epargne', 'autre'];

    if ($id_utilisateur <= 0) {
        $error_message = "Veuillez sélectionner un utilisateur propriétaire.";
    } elseif (empty($nom_compte)) {
        $error_message = "Le nom du compte est obligatoire.";
    } elseif (!in_array($type_compte, $types_autorises)) {
        $error_message = "Type de compte invalide.";
    } elseif (!is_numeric($solde)) {
        $error_message = "Le solde doit être une valeur numérique.";
    } elseif (empty($date_ouverture)) {
        $error_message = "La date d'ouverture est obligatoire.";
    } else {
        $update_sql = "UPDATE Compte_bancaire 
                       SET id_utilisateur = :id_utilisateur, 
                           nom_compte     = :nom_compte, 
                           type_compte    = :type_compte, 
                           solde          = :solde, 
                           date_ouverture = :date_ouverture 
                       WHERE id_compte = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            ':id_utilisateur' => $id_utilisateur,
            ':nom_compte'     => $nom_compte,
            ':type_compte'    => $type_compte,
            ':solde'          => $solde,
            ':date_ouverture' => $date_ouverture,
            ':id'             => $id
        ]);

        $_SESSION['success'] = "Le compte bancaire a été mis à jour avec succès.";
        header('Location: list.php');
        exit;
    }
}

$page_title = "Modifier le Compte Bancaire";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>✏️ Modifier le Compte Bancaire</h1>
        <p>Modification du compte #<?= htmlspecialchars($id) ?></p>
    </div>
    <a href="list.php" class="btn btn-secondary">⬅️ Retour à la liste</a>
</div>

<div class="card form-card">
    <form action="edit.php?id=<?= $id ?>" method="POST">
        
        <div class="form-group">
            <label for="id_utilisateur">Utilisateur propriétaire <span class="required">*</span></label>
            <select name="id_utilisateur" id="id_utilisateur" class="form-control" required>
                <option value="">-- Sélectionnez un utilisateur --</option>
                <?php foreach ($utilisateurs as $u): ?>
                    <option value="<?= $u['id_utilisateur'] ?>" <?= ($id_utilisateur == $u['id_utilisateur']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nom_compte">Nom du compte <span class="required">*</span></label>
                <input type="text" id="nom_compte" name="nom_compte" class="form-control" 
                       value="<?= htmlspecialchars($nom_compte) ?>" required>
            </div>

            <div class="form-group">
                <label for="type_compte">Type de compte <span class="required">*</span></label>
                <select name="type_compte" id="type_compte" class="form-control" required>
                    <option value="courant" <?= ($type_compte === 'courant') ? 'selected' : '' ?>>Courant</option>
                    <option value="epargne" <?= ($type_compte === 'epargne') ? 'selected' : '' ?>>Épargne</option>
                    <option value="autre"   <?= ($type_compte === 'autre') ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="solde">Solde (€) <span class="required">*</span></label>
                <input type="number" step="0.01" id="solde" name="solde" class="form-control" 
                       value="<?= htmlspecialchars($solde) ?>" required>
            </div>

            <div class="form-group">
                <label for="date_ouverture">Date d'ouverture <span class="required">*</span></label>
                <input type="date" id="date_ouverture" name="date_ouverture" class="form-control" 
                       value="<?= htmlspecialchars($date_ouverture) ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <a href="list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" name="submit" class="btn btn-primary">Mettre à jour le compte</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
