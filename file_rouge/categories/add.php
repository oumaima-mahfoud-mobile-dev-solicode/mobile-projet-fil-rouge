<?php
// =====================================================================
// Fichier : categories/add.php
// Description : Ajout d'une nouvelle catégorie (dépense ou revenu)
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

$error_message = '';
$nom_categorie = '';
$type          = 'depense';
$icone         = '';

if (isset($_POST['submit'])) {
    $nom_categorie = trim($_POST['nom_categorie'] ?? '');
    $type          = $_POST['type'] ?? 'depense';
    $icone         = trim($_POST['icone'] ?? '');

    $types_autorises = ['depense', 'revenu'];

    if (empty($nom_categorie)) {
        $error_message = "Le nom de la catégorie est obligatoire.";
    } elseif (!in_array($type, $types_autorises)) {
        $error_message = "Type de catégorie non valide (doit être 'dépense' ou 'revenu').";
    } else {
        $sql = "INSERT INTO Categorie_depense (nom_categorie, type, icone) 
                VALUES (:nom, :type, :icone)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom'   => $nom_categorie,
            ':type'  => $type,
            ':icone' => !empty($icone) ? $icone : null
        ]);

        $_SESSION['success'] = "La catégorie '$nom_categorie' a été ajoutée avec succès.";
        header('Location: list.php');
        exit;
    }
}

$page_title = "Nouvelle Catégorie";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>➕ Nouvelle Catégorie</h1>
        <p>Ajouter une catégorie pour classifier vos transactions</p>
    </div>
    <a href="list.php" class="btn btn-secondary">⬅️ Retour à la liste</a>
</div>

<div class="card form-card">
    <form action="add.php" method="POST">
        <div class="form-group">
            <label for="nom_categorie">Nom de la catégorie <span class="required">*</span></label>
            <input type="text" id="nom_categorie" name="nom_categorie" class="form-control" 
                   value="<?= htmlspecialchars($nom_categorie) ?>" required autofocus placeholder="Ex: Restaurant, Factures, Prime...">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="type">Type d'opération <span class="required">*</span></label>
                <select name="type" id="type" class="form-control" required>
                    <option value="depense" <?= ($type === 'depense') ? 'selected' : '' ?>>Dépense (débit)</option>
                    <option value="revenu"  <?= ($type === 'revenu') ? 'selected' : '' ?>>Revenu (crédit)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="icone">Icône / Libellé court (optionnel)</label>
                <input type="text" id="icone" name="icone" class="form-control" 
                       value="<?= htmlspecialchars($icone) ?>" placeholder="Ex: utensils, car, home...">
            </div>
        </div>

        <div class="form-actions">
            <a href="list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" name="submit" class="btn btn-primary">Enregistrer la catégorie</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
