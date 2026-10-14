<?php
// =====================================================================
// Fichier : includes/header.php
// Description : Entête commun, navigation et messages de retour
// =====================================================================

// Démarrer la session PHP si ce n'est pas déjà fait (pour gérer les messages flash)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculer le chemin relatif vers la racine du projet
if (!isset($base_path)) {
    // Si config/db.php est accessible directement, on est à la racine, sinon dans un sous-dossier
    $base_path = file_exists('config/db.php') ? '' : '../';
}

// Récupérer le nom du dossier courant pour marquer le menu actif
$current_page = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Gestion Finances</title>
    <!-- Feuille de style CSS principale -->
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
</head>
<body>

    <!-- Barre de navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?= $base_path ?>index.php" class="logo">
                💰 <span>FinanceApp</span>
            </a>
            <ul class="nav-links">
                <li>
                    <a href="<?= $base_path ?>index.php" class="<?= (strpos($current_page, 'index.php') !== false) ? 'active' : '' ?>">
                        📊 Tableau de bord
                    </a>
                </li>
                <li>
                    <a href="<?= $base_path ?>utilisateurs/list.php" class="<?= (strpos($current_page, 'utilisateurs') !== false) ? 'active' : '' ?>">
                        👤 Utilisateurs
                    </a>
                </li>
                <li>
                    <a href="<?= $base_path ?>comptes/list.php" class="<?= (strpos($current_page, 'comptes') !== false) ? 'active' : '' ?>">
                        🏦 Comptes
                    </a>
                </li>
                <li>
                    <a href="<?= $base_path ?>categories/list.php" class="<?= (strpos($current_page, 'categories') !== false) ? 'active' : '' ?>">
                        🏷️ Catégories
                    </a>
                </li>
                <li>
                    <a href="<?= $base_path ?>transactions/list.php" class="<?= (strpos($current_page, 'transactions') !== false) ? 'active' : '' ?>">
                        💳 Transactions
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Conteneur principal -->
    <main class="container">

        <!-- Affichage des messages de succès ou d'erreur (Session Flash) -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <span>✅ <?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <span>❌ <?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <span>❌ <?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>
