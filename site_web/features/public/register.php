<?php

session_start();

require_once '../../fonctions/db.php';

$conn = getConnexion();

$BASE_URL = '/site_web';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "Un compte existe déjà avec cet email.";
    } else {

        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $mot_de_passe, $role]);

        $id = $conn->lastInsertId();

        $_SESSION['utilisateur'] = [
            'id' => $id,
            'nom' => $nom,
            'email' => $email,
            'role' => $role
        ];

        header("Location: $BASE_URL/features/public/espaces/{$role}/index.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <form class="p-4 bg-white rounded shadow" method="POST">
        <h2 class="mb-4">Créer un compte</h2>

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" required class="form-control">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse Email</label>
            <input type="email" name="email" required class="form-control">
        </div>

        <div class="mb-3">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <input type="password" name="mot_de_passe" required class="form-control">
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Rôle</label>
            <select name="role" class="form-select" required>
                <option value="client">Client</option>
                <option value="livreur">Livreur</option>
                <option value="commercant">Commerçant</option>
                <option value="prestataire">Prestataire</option>
            </select>
        </div>

        <button class="btn btn-primary w-100">S'inscrire</button>
        <a href="login.php" class="d-block text-center mt-3">Déjà un compte ?</a>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../../fonctions/darkmode.php'; ?>

</body>
</html>