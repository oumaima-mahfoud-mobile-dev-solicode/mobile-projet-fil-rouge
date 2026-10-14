// =====================================================================
// Fichier : assets/js/script.js
// Description : Fonctions JavaScript vanilla simples (confirmation, helpers)
// =====================================================================

document.addEventListener('DOMContentLoaded', function () {
    // Sélectionner tous les boutons ou liens de suppression ayant la classe 'btn-delete'
    const deleteButtons = document.querySelectorAll('.btn-delete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            const message = this.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
            // Affichage de la boîte de dialogue standard confirm()
            if (!confirm(message)) {
                // Si l'utilisateur clique sur "Annuler", on bloque l'action
                event.preventDefault();
            }
        });
    });
});
