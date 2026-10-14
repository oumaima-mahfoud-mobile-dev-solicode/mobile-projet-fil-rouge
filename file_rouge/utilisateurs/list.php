<?php
// =====================================================================
// Fichier : utilisateurs/list.php
// Description : Liste de tous les utilisateurs enregistrés
// =====================================================================

// 1. Connexion à la base de données
require_once __DIR__ . '/../config/db.php';

// 2. Requête SQL pour récupérer tous les utilisateurs
$sql = "SELECT id_utilisateur, nom, prenom, email, date_creation 
        FROM Utilisateur 
        ORDER BY id_utilisateur ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$utilisateurs = $stmt->fetchAll();

// 3. Définir le titre de la page et inclure le header
$page_title = "Gestion des Utilisateurs";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>👤 Utilisateurs</h1>
        <p>Gérez les profils des utilisateurs de la plateforme</p>
    </div>
    <a href="add.php" class="btn btn-primary">➕ Nouvel Utilisateur</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Date d'inscription</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($utilisateurs)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Aucun utilisateur trouvé. Cliquez sur "Nouvel Utilisateur" pour en créer un.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($utilisateurs as $u): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($u['id_utilisateur']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($u['date_creation'])) ?></td>
                            <td style="text-align: right;">
                                <div class="actions-group" style="justify-content: flex-end;">
                                    <a href="edit.php?id=<?= $u['id_utilisateur'] ?>" class="btn btn-secondary btn-sm">
                                        ✏️ Modifier
                                    </a>
                                    <a href="delete.php?id=<?= $u['id_utilisateur'] ?>" 
                                       class="btn btn-danger btn-sm btn-delete" 
                                       data-confirm="Êtes-vous sûr de vouloir supprimer <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?> ? (Ses comptes et transactions liés seront également supprimés)">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// 4. Inclure le pied de page
require_once __DIR__ . '/../includes/footer.php';
?>
