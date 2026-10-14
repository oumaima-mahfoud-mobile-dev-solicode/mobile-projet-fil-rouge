<?php
// =====================================================================
// Fichier : index.php
// Description : Tableau de bord principal avec statistiques globales
// =====================================================================

require_once __DIR__ . '/config/db.php';

// 1. Calcul des statistiques globales
$total_utilisateurs = $pdo->query("SELECT COUNT(*) FROM Utilisateur")->fetchColumn();
$total_comptes      = $pdo->query("SELECT COUNT(*) FROM Compte_bancaire")->fetchColumn();
$solde_total        = $pdo->query("SELECT COALESCE(SUM(solde), 0) FROM Compte_bancaire")->fetchColumn();
$total_transactions = $pdo->query("SELECT COUNT(*) FROM Transaction")->fetchColumn();

// 2. Récupérer les 5 dernières transactions avec leurs détails
$sql_recent = "SELECT 
                    t.*, 
                    c.nom_compte, 
                    cat.nom_categorie, 
                    u.nom AS user_nom, 
                    u.prenom AS user_prenom
               FROM Transaction t
               INNER JOIN Compte_bancaire c ON t.id_compte = c.id_compte
               INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
               INNER JOIN Categorie_depense cat ON t.id_categorie = cat.id_categorie
               ORDER BY t.date_transaction DESC, t.id_transaction DESC
               LIMIT 5";
$recent_transactions = $pdo->query($sql_recent)->fetchAll();

// 3. Récupérer les comptes bancaires avec leurs soldes
$sql_comptes = "SELECT c.*, u.nom AS user_nom, u.prenom AS user_prenom
                FROM Compte_bancaire c
                INNER JOIN Utilisateur u ON c.id_utilisateur = u.id_utilisateur
                ORDER BY c.solde DESC";
$comptes_list = $pdo->query($sql_comptes)->fetchAll();

$page_title = "Tableau de Bord";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>📊 Tableau de Bord</h1>
        <p>Aperçu en temps réel de votre situation financière</p>
    </div>
    <div class="actions-group">
        <a href="transactions/add.php" class="btn btn-primary">➕ Nouvelle Transaction</a>
        <a href="comptes/add.php" class="btn btn-secondary">🏦 Nouveau Compte</a>
    </div>
</div>

<!-- Grille des statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div class="stat-info">
            <h3>Solde total disponible</h3>
            <div class="stat-value"><?= number_format($solde_total, 2, ',', ' ') ?> €</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">🏦</div>
        <div class="stat-info">
            <h3>Comptes bancaires</h3>
            <div class="stat-value"><?= $total_comptes ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">💳</div>
        <div class="stat-info">
            <h3>Transactions totales</h3>
            <div class="stat-value"><?= $total_transactions ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">👤</div>
        <div class="stat-info">
            <h3>Utilisateurs</h3>
            <div class="stat-value"><?= $total_utilisateurs ?></div>
        </div>
    </div>
</div>

<!-- Dernières transactions -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1.25rem;">🕒 Dernières transactions</h2>
        <a href="transactions/list.php" class="btn btn-secondary btn-sm">Voir tout l'historique ➡️</a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Compte</th>
                    <th>Catégorie</th>
                    <th>Type</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Aucune transaction enregistrée pour le moment.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_transactions as $t): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($t['date_transaction'])) ?></td>
                            <td><strong><?= htmlspecialchars($t['description'] ?: 'Sans libellé') ?></strong></td>
                            <td><?= htmlspecialchars($t['nom_compte']) ?> (<?= htmlspecialchars($t['user_prenom']) ?>)</td>
                            <td>🏷️ <?= htmlspecialchars($t['nom_categorie']) ?></td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($t['type_transaction']) ?>">
                                    <?= ($t['type_transaction'] === 'credit') ? 'Crédit' : 'Débit' ?>
                                </span>
                            </td>
                            <td>
                                <span class="amount-<?= htmlspecialchars($t['type_transaction']) ?>">
                                    <?= ($t['type_transaction'] === 'credit') ? '+' : '-' ?>
                                    <?= number_format($t['montant'], 2, ',', ' ') ?> €
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Liste des comptes avec soldes -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1.25rem;">🏦 Solde par compte bancaire</h2>
        <a href="comptes/list.php" class="btn btn-secondary btn-sm">Gérer les comptes ➡️</a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Compte</th>
                    <th>Titulaire</th>
                    <th>Type</th>
                    <th>Solde</th>
                    <th>Date d'ouverture</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comptes_list)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Aucun compte bancaire configuré.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comptes_list as $c): ?>
                        <tr>
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
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
