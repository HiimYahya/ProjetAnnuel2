<?php
include '../../fonctions/db.php';
include '../../fonctions/email.php';

$conn = getConnexion();

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE token_verification = ? AND email_verifie = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $stmt = $conn->prepare("UPDATE utilisateurs SET email_verifie = 1, date_verification = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        $message = "Votre email a été vérifié avec succès ! Vous pouvez maintenant vous connecter.";
    } else {
        $message = "Token invalide ou email déjà vérifié.";
    }
} else {
    $message = "Token manquant.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification d'email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="p-4 bg-white rounded shadow text-center">
        <h2 class="mb-4">Vérification d'email</h2>
        <p><?php echo $message; ?></p>
        <a href="login.php" class="btn btn-primary">Se connecter</a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../../fonctions/darkmode.php'; ?>
</body>
</html> 