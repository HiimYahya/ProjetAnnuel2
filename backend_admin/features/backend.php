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
    <title>Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="features.css">
    <script src="../assets/js/color-modes.js"></script>
</head>

<?php include '../fonctions/header.php'; ?>

<body class="d-flex flex-column min-vh-100">
<div class="container py-4">
    <h1 class="mb-4">Backend : Vue d'ensemble</h1>

    <h3 class="mt-5">Livreurs</h3>
    <?php afficherAvecLimite($conn, 'livreurs', 'id, nom, statut', 5, 'DESC'); ?>
    <a href='livreurs.php'class='btn btn-primary btn-sm'>Afficher plus</a>

    <h3 class="mt-5">Livraisons</h3>
    <?php afficherAvecLimite($conn, 'livraisons', 'id, id_client, id_livreur, statut', 5, 'DESC'); ?>
    <a href='livraisons.php'class='btn btn-primary btn-sm'>Afficher plus</a>

    <h3 class="mt-5">Clients</h3>
    <?php afficherAvecLimite($conn, 'clients', 'id, nom, adresse', 5, 'DESC'); ?>
    <a href='clients.php'class='btn btn-primary btn-sm'>Afficher plus</a>

    <h3 class="mt-5">Prestataires</h3>
    <?php afficherAvecLimite($conn, 'prestataires', 'id, nom, service', 5, 'DESC'); ?>
    <a href='prestataires.php'class='btn btn-primary btn-sm'>Afficher plus</a>

    <h3 class="mt-5">Paiements</h3>
    <?php afficherAvecLimite($conn, 'paiements', 'id, montant, methode, statut', 5, 'DESC'); ?>
    <a href='paiements.php'class='btn btn-primary btn-sm'>Afficher plus</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../fonctions/darkmode.php'; ?>
<?php include '../fonctions/footer.php'; ?>

</body>
</html>
