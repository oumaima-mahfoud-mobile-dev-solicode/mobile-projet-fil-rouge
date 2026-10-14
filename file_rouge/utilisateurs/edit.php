<?php
// =====================================================================
// Fichier : utilisateurs/edit.php
// Description : Modification d'un utilisateur existant
// =====================================================================

session_start();
require_once __DIR__ . '/../config/db.php';

// 1. Récupération de l'identifiant depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Identifiant d'utilisateur invalide.";
    header('Location: list.php');
    exit;
}

// 2. Recherche de l'utilisateur existant
$stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = :id");
$stmt->execute([':id' => $id]);
$utilisateur = $stmt->fetch();

if (!$utilisateur) {
    $_SESSION['error'] = "Utilisateur introuvable.";
    header('Location: list.php');
    exit;
}

$error_message = '';
$nom = $utilisateur['nom'];
$prenom = $utilisateur['prenom'];
$email = $utilisateur['email'];

// 3. Traitement de la mise à jour lors de la soumission
if (isset($_POST['submit'])) {
    $nom          = trim($_POST['nom'] ?? '');
    $prenom       = trim($_POST['prenom'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $nouveau_pass = $_POST['nouveau_mot_de_passe'] ?? '';

    // Validation des données obligatoires
    if (empty($nom) || empty($prenom) || empty($email)) {
        $error_message = "Le nom, le prénom et l'email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "L'adresse email est invalide.";
    } else {
        // Vérification de l'unicité de l'email pour les autres utilisateurs
        $check_stmt = $pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email = :email AND id_utilisateur != :id");
        $check_stmt->execute([':email' => $email, ':id' => $id]);

        if ($check_stmt->fetch()) {
            $error_message = "Cet email est déjà attribué à un autre compte.";
        } else {
            // Si l'utilisateur a saisi un nouveau mot de passe, on le met à jour
            if (!empty($nouveau_pass)) {
                if (strlen($nouveau_pass) < 6) {
                    $error_message = "Le nouveau mot de passe doit comporter au moins 6 caractères.";
                } else {
                    $pass_hache = password_hash($nouveau_pass, PASSWORD_BCRYPT);
                    $update_sql = "UPDATE Utilisateur 
                                   SET nom = :nom, prenom = :prenom, email = :email, mot_de_passe = :pass 
                                   WHERE id_utilisateur = :id";
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute([
                        ':nom'   => $nom,
                        ':prenom'=> $prenom,
                        ':email' => $email,
                        ':pass'  => $pass_hache,
                        ':id'    => $id
                    ]);
                }
            } else {
                // Mise à jour sans toucher au mot de passe existant
                $update_sql = "UPDATE Utilisateur 
                               SET nom = :nom, prenom = :prenom, email = :email 
                               WHERE id_utilisateur = :id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    ':nom'   => $nom,
                    ':prenom'=> $prenom,
                    ':email' => $email,
                    ':id'    => $id
                ]);
            }

            if (empty($error_message)) {
                $_SESSION['success'] = "L'utilisateur a été mis à jour avec succès.";
                header('Location: list.php');
                exit;
            }
        }
    }
}

$page_title = "Modifier l'Utilisateur";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>✏️ Modifier l'Utilisateur</h1>
        <p>Modification du profil #<?= htmlspecialchars($id) ?></p>
    </div>
    <a href="list.php" class="btn btn-secondary">⬅️ Retour à la liste</a>
</div>

<div class="card form-card">
    <form action="edit.php?id=<?= $id ?>" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="prenom">Prénom <span class="required">*</span></label>
                <input type="text" id="prenom" name="prenom" class="form-control" 
                       value="<?= htmlspecialchars($prenom) ?>" required>
            </div>

            <div class="form-group">
                <label for="nom">Nom <span class="required">*</span></label>
                <input type="text" id="nom" name="nom" class="form-control" 
                       value="<?= htmlspecialchars($nom) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Adresse Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="form-group">
            <label for="nouveau_mot_de_passe">Nouveau mot de passe (optionnel)</label>
            <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" class="form-control" 
                   placeholder="Laisser vide pour conserver le mot de passe actuel">
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
