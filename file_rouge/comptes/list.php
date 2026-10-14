<?php
// =====================================================================
// Fichier : comptes/list.php
// Description : Liste des comptes bancaires avec jointure sur l'utilisateur
// =====================================================================

require_once __DIR__ . '/../config/db.php';

// Requête SQL avec JOIN pour récupérer le nom et prénom de l'utilisateur propriétaire
$sql = "SELECT c.*, u.nom AS user_nom, u.prenom AS user_prenom 
        FROM Compte_bancaire c
        INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
        ORDER BY c.id_compte ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$comptes = $stmt->fetchAll();

$page_title = "Gestion des Comptes Bancaires";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>🏦 Comptes Bancaires</h1>
        <p>Consultez et gérez les comptes bancaires et leurs soldes</p>
    </div>
    <a href="add.php" class="btn btn-primary">➕ Nouveau Compte</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du compte</th>
                    <th>Propriétaire</th>
                    <th>Type</th>
                    <th>Solde actuel</th>
                    <th>Date d'ouverture</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comptes)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Aucun compte bancaire enregistré. Cliquez sur "Nouveau Compte" pour en ajouter un.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comptes as $c): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($c['id_compte']) ?></td>
                            <td><strong><?= htmlspecialchars($c['nom_compte']) ?></strong></td>
                            <td><?= htmlspecialchars($c['user_prenom'] . ' ' . $c['user_nom']) ?></td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($c['type_compte']) ?>">
                                    <?= htmlspecialchars(ucfirst($c['type_compte'])) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: <?= $c['solde'] >= 0 ? 'var(--dark)' : 'var(--danger)' ?>;">
                                    <?= number_format($c['solde'], 2, ',', ' ') ?> €
                                </strong>
                            </td>
                            <td><?= date('d/m/Y', strtotime($c['date_ouverture'])) ?></td>
                            <td style="text-align: right;">
                                <div class="actions-group" style="justify-content: flex-end;">
                                    <a href="edit.php?id=<?= $c['id_compte'] ?>" class="btn btn-secondary btn-sm">
                                        ✏️ Modifier
                                    </a>
                                    <a href="delete.php?id=<?= $c['id_compte'] ?>" 
                                       class="btn btn-danger btn-sm btn-delete" 
                                       data-confirm="Êtes-vous sûr de vouloir supprimer ce compte (<?= htmlspecialchars($c['nom_compte']) ?>) ? Ses transactions seront également supprimées.">
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
