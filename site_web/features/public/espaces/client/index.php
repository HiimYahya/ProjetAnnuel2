<?php
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    header('Location: ../../../../public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php include '../../../../fonctions/header_client.php'; ?> 

<body class="d-flex flex-column min-vh-100">
    <?php include '../../../../fonctions/header_client.php'; ?>
    <div class="container-fluid">
        <div class="page-title">
            <h1>Tableau de bord Client</h1>
        </div>
        <!-- Message de bienvenue avec nom utilisateur -->
        <div class="alert alert-success mb-4">
          <h4 class="alert-heading">Bienvenue <?php echo htmlspecialchars($_SESSION['utilisateur']['nom']); ?> !</h4>
          <p>Vous pouvez consulter vos annonces, vos livraisons en cours, vos livraisons livrées et valider les livraisons à réception.</p>
        </div>
        <div class="row g-4 mb-4" id="dashboard-cards">
            <div class="col-md-4">
                <div class="card text-bg-primary shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-5" id="stat-annonces">0</div>
                        <div>Annonces créées</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-success shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-5" id="stat-livraisons-encours">0</div>
                        <div>Livraisons en cours</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-secondary shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-5" id="stat-livraisons-livrees">0</div>
                        <div>Livraisons livrées</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Carte annonces -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Mes annonces</h5>
                    </div>
                    <div class="card-body">
                        <p>Consultez et gérez vos annonces de livraison.</p>
                        <a href="annonces.php" class="btn btn-outline-primary mt-2">Voir mes annonces</a>
                    </div>
                </div>
            </div>
            <!-- Carte livraisons -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Mes livraisons</h5>
                    </div>
                    <div class="card-body">
                        <p>Consultez et suivez vos livraisons en cours ou livrées.</p>
                        <a href="livraisons.php" class="btn btn-outline-success mt-2">Voir mes livraisons</a>
                    </div>
                </div>
            </div>
            <!-- Carte calendrier -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Calendrier</h5>
                    </div>
                    <div class="card-body">
                        <p>Consultez votre planning de livraisons à venir.</p>
                        <a href="calendrier.php" class="btn btn-outline-secondary mt-2">Voir mon calendrier</a>
                    </div>
                </div>
            </div>
        </div>
        <div id="dashboard-validations" class="mt-4"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../assets/js/darkmode.js"></script>
    <script>
    fetch('/site_web/api/client/dashboard/get.php', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            document.getElementById('stat-annonces').textContent = data.annonces || 0;
            document.getElementById('stat-livraisons-encours').textContent = data.livraisons_en_cours || 0;
            document.getElementById('stat-livraisons-livrees').textContent = data.livraisons_livrees || 0;
        });
    // Affichage des validations en attente
    fetch('/site_web/api/client/livraisons/get.php', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('dashboard-validations');
            if (!data.livraisons || !data.livraisons.length) {
                div.innerHTML = '<div class="alert alert-info">Aucune livraison en attente de validation.</div>';
                return;
            }
            const enAttente = data.livraisons.filter(l => l.validation_client == 0);
            if (!enAttente.length) {
                div.innerHTML = '<div class="alert alert-success">Toutes vos livraisons sont validées.</div>';
                return;
            }
            let html = `<div class="alert alert-warning"><strong>Livraisons à valider :</strong><ul class='mb-0'>`;
            enAttente.forEach(l => {
                const arrivee = l.adresse_arrivee_affichee || l.ville_arrivee;
                html += `<li class='mb-2'><strong>${l.titre}</strong> <span class='text-muted'>(${l.ville_depart} → ${arrivee})</span> <a href="/site_web/features/public/espaces/client/livraisons.php?id=${l.id_annonce}" class="btn btn-sm btn-success ms-2">Valider</a></li>`;
            });
            html += '</ul></div>';
            div.innerHTML = html;
        });
    </script>
    <?php include '../../../../fonctions/footer.php'; ?>

</body>
</html>
