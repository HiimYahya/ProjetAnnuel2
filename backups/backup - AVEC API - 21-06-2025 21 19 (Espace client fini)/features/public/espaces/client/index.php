<?php
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    header('Location: ../../../../public/login.php');
    exit;
}
?>

<!doctype html>
<html lang="fr" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <title>Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="features.css">
</head>

<?php include '../../../../fonctions/header_client.php'; ?> 

<body class="d-flex flex-column min-vh-100">
    <div class="container py-5">
        <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['utilisateur']['nom']); ?> dans votre espace Client</h1>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../assets/js/darkmode.js"></script>
    <?php include '../../../../fonctions/footer.php'; ?>

</body>
</html>
