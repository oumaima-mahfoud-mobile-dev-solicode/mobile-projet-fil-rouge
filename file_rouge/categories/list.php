<?php
// =====================================================================
// Fichier : categories/list.php
// Description : Liste des catégories de dépenses et de revenus
// =====================================================================

require_once __DIR__ . '/../config/db.php';

// Récupération de toutes les catégories
$stmt = $pdo->query("SELECT * FROM Categorie_depense ORDER BY type ASC, nom_categorie ASC");
$categories = $stmt->fetchAll();

$page_title = "Gestion des Catégories";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>🏷️ Catégories</h1>
        <p>Gérez les catégories utilisées pour classer vos dépenses et vos revenus</p>
    </div>
    <a href="add.php" class="btn btn-primary">➕ Nouvelle Catégorie</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de la catégorie</th>
                    <th>Type</th>
                    <th>Icône / Symbole</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Aucune catégorie enregistrée. Cliquez sur "Nouvelle Catégorie" pour en ajouter une.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($cat['id_categorie']) ?></td>
                            <td><strong><?= htmlspecialchars($cat['nom_categorie']) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($cat['type']) ?>">
                                    <?= htmlspecialchars(ucfirst($cat['type'])) ?>
                                </span>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($cat['icone'] ?: '-') ?></code>
                            </td>
                            <td style="text-align: right;">
                                <div class="actions-group" style="justify-content: flex-end;">
                                    <a href="edit.php?id=<?= $cat['id_categorie'] ?>" class="btn btn-secondary btn-sm">
                                        ✏️ Modifier
                                    </a>
                                    <a href="delete.php?id=<?= $cat['id_categorie'] ?>" 
                                       class="btn btn-danger btn-sm btn-delete" 
                                       data-confirm="Êtes-vous sûr de vouloir supprimer la catégorie '<?= htmlspecialchars($cat['nom_categorie']) ?>' ?">
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
require_once __DIR__ . '/../includes/footer.php';
?>
