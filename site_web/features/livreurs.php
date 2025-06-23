<?php

include '../fonctions/db.php'; 
include '../fonctions/fonctions.php';
include '../fonctions/icons.php';

$conn = getConnexion();

?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <title>Liste des livreurs</title>
    <script src="../assets/js/color-modes.js"></script>
</head>

<?php include '../fonctions/header.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="container-fluid">
        <div class="page-title">
            <h1>Liste des Livreurs</h1>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Livreurs</h5>
                    </div>
                    <div class="card-body">
                        <?php afficherAvecLimite($conn, 'livreurs', 'id, nom, statut', 1000, 'DESC'); ?>
                    </div>
                    <div class="card-footer text-end">
                        <a href='backend.php' class='btn btn-primary btn-sm'>Retour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/darkmode.js"></script>
<?php include '../fonctions/footer.php'; ?>

</html>