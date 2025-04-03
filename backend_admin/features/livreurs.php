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

    <div class="container py-4">
        <h1 class="mb-4">Liste des Livreurs</h1>
            <?php afficherAvecLimite($conn, 'livreurs', 'id, nom, statut', 1000, 'DESC'); ?>
            <a href='backend.php'class='btn btn-primary btn-sm'>Retour</a>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../fonctions/darkmode.php'; ?>
<?php include '../fonctions/footer.php'; ?>

</html>