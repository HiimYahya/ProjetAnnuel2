<?php

include '../../../../fonctions/db.php';
include '../../../../fonctions/fonctions.php';
include '../../../../fonctions/icons.php';

session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header('Location: ../../../../public/login.php');
    exit;
}

$conn = getConnexion();

?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <title>Tableau de bord Livreur - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include '../../../../fonctions/header_livreur.php'; ?>

    <div class="container-fluid">
        <div class="page-title">
            <h1>Tableau de bord Livreur</h1>
        </div>
        
        <!-- Message de bienvenue avec nom utilisateur -->
        <div class="alert alert-success mb-4">
          <h4 class="alert-heading">Bienvenue <?php echo htmlspecialchars($_SESSION['utilisateur']['nom']); ?> !</h4>
          <p>Vous pouvez consulter vos livraisons en cours, les livraisons livrées, les segments disponibles et les annonces disponibles.</p>
        </div>

        <div class="row g-4 mb-4" id="dashboard-cards">
          <div class="col-md-4">
            <div class="card text-bg-primary shadow-sm h-100">
              <div class="card-body text-center">
                <div class="display-5" id="stat-livraisons-encours">0</div>
                <div>Livraisons en cours</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card text-bg-success shadow-sm h-100">
              <div class="card-body text-center">
                <div class="display-5" id="stat-livraisons-livrees">0</div>
                <div>Livraisons livrées</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card text-bg-warning shadow-sm h-100">
              <div class="card-body text-center">
                <div class="display-5" id="stat-segments">0</div>
                <div>Segments à prendre</div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="page-content">
            
            <div class="row">
                <!-- Carte segments disponibles -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Segments disponibles</h5>
                        </div>
                        <div class="card-body">
                            <p>Consultez et récupérez des segments de livraison disponibles en points relais.</p>
                            <a href="segments_disponibles.php" class="btn btn-outline-primary mt-2">Voir les segments</a>
                        </div>
                    </div>
                </div>
                
                <!-- Carte annonces disponibles -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Annonces disponibles</h5>
                        </div>
                        <div class="card-body">
                            <p>Consultez et acceptez de nouvelles annonces de livraison à prendre en charge.</p>
                            <a href="annonces_dispo.php" class="btn btn-outline-primary mt-2">Voir les annonces</a>
                        </div>
                    </div>
                </div>
                
                <!-- Carte mes livraisons -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Mes livraisons</h5>
                        </div>
                        <div class="card-body">
                            <p>Consultez et gérez vos livraisons et segments en cours.</p>
                            <a href="mes_livraisons.php" class="btn btn-outline-primary mt-2">Mes livraisons</a>
                        </div>
                    </div>
                </div>
                
                <!-- Carte calendrier -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Calendrier</h5>
                        </div>
                        <div class="card-body">
                            <p>Consultez votre planning de livraisons à venir.</p>
                            <a href="calendrier.php" class="btn btn-outline-primary mt-2">Voir mon calendrier</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
    // Stats principales
    fetch('/site_web/api/livreur/livraisons/get.php', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        let enCours = 0, livrees = 0;
        if (data.livraisons && Array.isArray(data.livraisons)) {
          data.livraisons.forEach(l => {
            if (l.statut === 'en cours' || l.statut === 'prise en charge') enCours++;
            if (l.statut === 'livrée') livrees++;
          });
        }
        document.getElementById('stat-livraisons-encours').textContent = enCours;
        document.getElementById('stat-livraisons-livrees').textContent = livrees;
      });
    // Segments à prendre (annonces dispo)
    fetch('/site_web/api/livreur/annonces/get.php', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        let segments = 0;
        if (data.annonces && Array.isArray(data.annonces)) {
          segments = data.annonces.filter(a => a.segmentation_possible == 1).length;
        }
        document.getElementById('stat-segments').textContent = segments;
      });
    </script>
    <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
