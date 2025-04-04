<?php
include '../../fonctions/db.php';
$conn = getConnexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $email, $mot_de_passe, $role]);

    session_start();
    $_SESSION['utilisateur'] = [
    'id' => $conn->lastInsertId(),
    'nom' => $nom,
    'email' => $email,
    'role' => $role
];

// Redirection en fonction du rôle
switch ($role) {
    case 'client':
        header("Location: ../public/espaces/client/index.php");
        break;
    case 'livreur':
        header("Location: ../public/espaces/livreur/index.php");
        break;
    case 'commercant':
        header("Location: ../public/espaces/commercant/index.php");
        break;
    case 'prestataire':
        header("Location: ../public/espaces/prestataire/index.php");
        break;
    default:
        header("Location: login.php");
}
exit;

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
        <a href="login.php" class="d-block text-center mt-3">Déjà un compte ? Connexion</a>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../../fonctions/darkmode.php'; ?>
</body>
</html>