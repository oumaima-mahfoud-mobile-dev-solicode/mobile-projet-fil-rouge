<?php
// =====================================================================
// Fichier : includes/footer.php
// Description : Pied de page commun et chargement des scripts JS
// =====================================================================

// S'assurer que $base_path est défini
if (!isset($base_path)) {
    $base_path = file_exists('config/db.php') ? '' : '../';
}
?>
    </main>

    <!-- Pied de page -->
    <footer>
        <p>&copy; <?= date('Y') ?> - Application de Gestion de Finances Personnelles (Fil Rouge - Partie 1)</p>
    </footer>

    <!-- Script JavaScript pour les confirmations et interactions -->
    <script src="<?= $base_path ?>assets/js/script.js"></script>
</body>
</html>
