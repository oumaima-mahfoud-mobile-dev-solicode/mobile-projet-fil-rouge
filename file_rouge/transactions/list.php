<?php
// =====================================================================
// Fichier : transactions/list.php
// Description : Liste des transactions avec jointures (compte, catégorie, utilisateur)
// =====================================================================

require_once __DIR__ . '/../config/db.php';

// Requête SQL avec JOINs pour récupérer les libellés compréhensibles au lieu des IDs
$sql = "SELECT 
            t.*, 
            c.nom_compte, 
            cat.nom_categorie, 
            cat.icone,
            u.nom AS user_nom, 
            u.prenom AS user_prenom
        FROM Transaction t
        INNER JOIN Compte_bancaire c ON t.id_compte = c.id_compte
        INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
        INNER JOIN Categorie_depense cat ON t.id_categorie = cat.id_categorie
        ORDER BY t.date_transaction DESC, t.id_transaction DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$transactions = $stmt->fetchAll();

$page_title = "Gestion des Transactions";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>💳 Transactions</h1>
        <p>Historique des débits et des crédits enregistrés sur vos comptes</p>
    </div>
    <a href="add.php" class="btn btn-primary">➕ Nouvelle Transaction</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Compte bancaire</th>
                    <th>Catégorie</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Aucune transaction enregistrée. Cliquez sur "Nouvelle Transaction" pour commencer.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($t['date_transaction'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($t['description'] ?: 'Sans description') ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['nom_compte']) ?>
                                <small style="display: block; color: var(--text-muted);">
                                    (<?= htmlspecialchars($t['user_prenom'] . ' ' . $t['user_nom']) ?>)
                                </small>
                            </td>
                            <td>
                                <span title="<?= htmlspecialchars($t['icone'] ?? '') ?>">
                                    🏷️ <?= htmlspecialchars($t['nom_categorie']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($t['type_transaction']) ?>">
                                    <?= ($t['type_transaction'] === 'credit') ? 'Crédit (+)' : 'Débit (-)' ?>
                                </span>
                            </td>
                            <td>
                                <span class="amount-<?= htmlspecialchars($t['type_transaction']) ?>">
                                    <?= ($t['type_transaction'] === 'credit') ? '+' : '-' ?>
                                    <?= number_format($t['montant'], 2, ',', ' ') ?> €
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="actions-group" style="justify-content: flex-end;">
                                    <a href="edit.php?id=<?= $t['id_transaction'] ?>" class="btn btn-secondary btn-sm">
                                        ✏️ Modifier
                                    </a>
                                    <a href="delete.php?id=<?= $t['id_transaction'] ?>" 
                                       class="btn btn-danger btn-sm btn-delete" 
                                       data-confirm="Êtes-vous sûr de vouloir supprimer cette transaction de <?= number_format($t['montant'], 2, ',', ' ') ?> € ? Le solde du compte sera réajusté.">
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
