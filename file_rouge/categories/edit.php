<?php
// =====================================================================
// Fichier : categories/edit.php
// Description : Modification d'une catégorie
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Identifiant de catégorie invalide.";
    header('Location: list.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Categorie_depense WHERE id_categorie = :id");
$stmt->execute([':id' => $id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    $_SESSION['error'] = "Catégorie introuvable.";
    header('Location: list.php');
    exit;
}

$error_message = '';
$nom_categorie = $categorie['nom_categorie'];
$type          = $categorie['type'];
$icone         = $categorie['icone'];

if (isset($_POST['submit'])) {
    $nom_categorie = trim($_POST['nom_categorie'] ?? '');
    $type          = $_POST['type'] ?? 'depense';
    $icone         = trim($_POST['icone'] ?? '');

    $types_autorises = ['depense', 'revenu'];

    if (empty($nom_categorie)) {
        $error_message = "Le nom de la catégorie est obligatoire.";
    } elseif (!in_array($type, $types_autorises)) {
        $error_message = "Type de catégorie non valide.";
    } else {
        $update_sql = "UPDATE Categorie_depense 
                       SET nom_categorie = :nom, type = :type, icone = :icone 
                       WHERE id_categorie = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            ':nom'   => $nom_categorie,
            ':type'  => $type,
            ':icone' => !empty($icone) ? $icone : null,
            ':id'    => $id
        ]);

        $_SESSION['success'] = "La catégorie a été mise à jour avec succès.";
        header('Location: list.php');
        exit;
    }
}

$page_title = "Modifier la Catégorie";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>✏️ Modifier la Catégorie</h1>
        <p>Modification de la catégorie #<?= htmlspecialchars($id) ?></p>
    </div>
    <a href="list.php" class="btn btn-secondary">⬅️ Retour à la liste</a>
</div>

<div class="card form-card">
    <form action="edit.php?id=<?= $id ?>" method="POST">
        <div class="form-group">
            <label for="nom_categorie">Nom de la catégorie <span class="required">*</span></label>
            <input type="text" id="nom_categorie" name="nom_categorie" class="form-control" 
                   value="<?= htmlspecialchars($nom_categorie) ?>" required>
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
                <label for="icone">Icône / Libellé court</label>
                <input type="text" id="icone" name="icone" class="form-control" 
                       value="<?= htmlspecialchars($icone ?? '') ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" name="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
