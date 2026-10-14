<?php
// =====================================================================
// Fichier : utilisateurs/add.php
// Description : Formulaire et traitement d'ajout d'un nouvel utilisateur
// =====================================================================

// 1. Démarrer la session et charger la connexion PDO
session_start();
require_once __DIR__ . '/../config/db.php';

$error_message = '';
$nom = '';
$prenom = '';
$email = '';

// 2. Traitement du formulaire lors de la soumission
if (isset($_POST['submit'])) {
    $nom           = trim($_POST['nom'] ?? '');
    $prenom        = trim($_POST['prenom'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $mot_de_passe  = $_POST['mot_de_passe'] ?? '';

    // Validation des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe)) {
        $error_message = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Le format de l'adresse email est invalide.";
    } elseif (strlen($mot_de_passe) < 6) {
        $error_message = "Le mot de passe doit comporter au moins 6 caractères.";
    } else {
        // Vérifier si l'adresse email existe déjà
        $check_sql = "SELECT id_utilisateur FROM Utilisateur WHERE email = :email";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([':email' => $email]);

        if ($check_stmt->fetch()) {
            $error_message = "Cette adresse email est déjà utilisée par un autre utilisateur.";
        } else {
            // Hachage sécurisé du mot de passe avec l'algorithme BCRYPT
            $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_BCRYPT);

            // Insertion en base de données avec une requête préparée
            $insert_sql = "INSERT INTO Utilisateur (nom, prenom, email, mot_de_passe) 
                           VALUES (:nom, :prenom, :email, :mot_de_passe)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([
                ':nom'          => $nom,
                ':prenom'       => $prenom,
                ':email'        => $email,
                ':mot_de_passe' => $mot_de_passe_hache
            ]);

            // Redirection vers la liste avec un message de succès
            $_SESSION['success'] = "L'utilisateur $prenom $nom a été créé avec succès.";
            header('Location: list.php');
            exit;
        }
    }
}

// 3. Affichage du formulaire HTML
$page_title = "Ajouter un Utilisateur";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>➕ Ajouter un Utilisateur</h1>
        <p>Créer un nouveau profil utilisateur</p>
    </div>
    <a href="list.php" class="btn btn-secondary">⬅️ Retour à la liste</a>
</div>

<div class="card form-card">
    <form action="add.php" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="prenom">Prénom <span class="required">*</span></label>
                <input type="text" id="prenom" name="prenom" class="form-control" 
                       value="<?= htmlspecialchars($prenom) ?>" required autofocus placeholder="Ex: Jean">
            </div>

            <div class="form-group">
                <label for="nom">Nom <span class="required">*</span></label>
                <input type="text" id="nom" name="nom" class="form-control" 
                       value="<?= htmlspecialchars($nom) ?>" required placeholder="Ex: Dupont">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Adresse Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?= htmlspecialchars($email) ?>" required placeholder="jean.dupont@example.com">
        </div>

        <div class="form-group">
            <label for="mot_de_passe">Mot de passe <span class="required">*</span></label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" 
                   required minlength="6" placeholder="Minimum 6 caractères">
        </div>

        <div class="form-actions">
            <a href="list.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" name="submit" class="btn btn-primary">Enregistrer l'utilisateur</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
