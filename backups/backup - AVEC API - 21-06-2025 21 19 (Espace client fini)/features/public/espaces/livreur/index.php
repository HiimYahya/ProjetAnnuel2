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
        
        <div class="page-content">
            <!-- Message de bienvenue avec nom utilisateur -->
            <div class="alert alert-success mb-4">
                <h4 class="alert-heading">Bienvenue <?php echo htmlspecialchars($_SESSION['utilisateur']['nom']); ?> !</h4>
                <p>Votre espace livreur vous permet d'accepter des livraisons ou de prendre en charge des segments.</p>
            </div>
            
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
    <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
