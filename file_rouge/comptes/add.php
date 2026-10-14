<?php
// =====================================================================
// Fichier : comptes/add.php
// Description : Ajout d'un nouveau compte bancaire
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

// Récupérer la liste des utilisateurs pour remplir la balise <select>
$users_stmt = $pdo->query("SELECT id_utilisateur, nom, prenom FROM Utilisateur ORDER BY prenom, nom ASC");
$utilisateurs = $users_stmt->fetchAll();

$error_message  = '';
$id_utilisateur = '';
$nom_compte     = '';
$type_compte    = 'courant';
$solde          = '0.00';
$date_ouverture = date('Y-m-d');

// Traitement du formulaire lors de la soumission
if (isset($_POST['submit'])) {
    $id_utilisateur = (int)($_POST['id_utilisateur'] ?? 0);
    $nom_compte     = trim($_POST['nom_compte'] ?? '');
    $type_compte    = $_POST['type_compte'] ?? 'courant';
    $solde          = trim($_POST['solde'] ?? '0.00');
    $date_ouverture = $_POST['date_ouverture'] ?? date('Y-m-d');

    // Validation des données
    $types_autorises = ['courant', 'epargne', 'autre'];

    if ($id_utilisateur <= 0) {
        $error_message = "Veuillez sélectionner un utilisateur propriétaire.";
    } elseif (empty($nom_compte)) {
        $error_message = "Le nom du compte est obligatoire.";
    } elseif (!in_array($type_compte, $types_autorises)) {
        $error_message = "Type de compte invalide.";
    } elseif (!is_numeric($solde)) {
        $error_message = "Le solde initial doit être une valeur numérique.";
    } elseif (empty($date_ouverture)) {
        $error_message = "La date d'ouverture est obligatoire.";
    } else {
        // Insertion en base de données avec requête préparée PDO
        $sql = "INSERT INTO Compte_bancaire (id_utilisateur, nom_compte, type_compte, solde, date_ouverture) 
                VALUES (:id_utilisateur, :nom_compte, :type_compte, :solde, :date_ouverture)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_utilisateur' => $id_utilisateur,
            ':nom_compte'     => $nom_compte,
            ':type_compte'    => $type_compte,
            ':solde'          => $solde,
            ':date_ouverture' => $date_ouverture
        ]);

        $_SESSION['success'] = "Le compte bancaire '$nom_compte' a été créé avec succès.";
        header('Location: list.php');
        exit;
    }
}

$page_title = "Nouveau Compte Bancaire";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>➕ Nouveau Compte Bancaire</h1>
        <p>Créer un compte et l'associer à un utilisateur</p>
    </div>
    <a href="list.php" class="btn btn-secondary">⬅️ Retour à la liste</a>
</div>

<div class="card form-card">
    <form action="add.php" method="POST">
        
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
                       value="<?= htmlspecialchars($nom_compte) ?>" required placeholder="Ex: Compte Courant, Livret A...">
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
                <label for="solde">Solde initial (€) <span class="required">*</span></label>
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
            <button type="submit" name="submit" class="btn btn-primary">Créer le compte</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
