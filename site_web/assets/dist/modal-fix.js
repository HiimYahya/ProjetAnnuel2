/**
 * Script pour corriger les problèmes de modal dans EcoDeli
 */
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour fixer les problèmes de modal
    function fixModals() {
        // S'assurer que toutes les modales ont le bon z-index
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.zIndex = '10000';
            
            // Assurer que tous les éléments interactifs sont cliquables
            modal.querySelectorAll('input, select, button, .form-check-label, a').forEach(el => {
                el.style.pointerEvents = 'auto';
                el.style.position = 'relative';
                el.style.zIndex = '10002';
            });
            
            // Assurer que le contenu de la modal est cliquable
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.pointerEvents = 'auto';
            }
            
            // Assurer que la modal est correctement configurée quand elle s'ouvre
            modal.addEventListener('shown.bs.modal', function() {
                // Assurer que la modal est visible
                modal.style.display = 'block';
                modal.style.zIndex = '10000';
                
                // Assurer que le backdrop est correctement positionné
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.style.zIndex = '9999';
                }
            });
        });
    }
    
    // Exécuter la fonction dès le chargement de la page
    fixModals();
    
    // Observer les changements dans le DOM pour fixer les modales ajoutées dynamiquement
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                fixModals();
            }
        });
    });
    
    // Observer tout le document pour les nouvelles modales
    observer.observe(document.body, { childList: true, subtree: true });
}); 