<?php
session_start();
include '../fonctions/db.php';
$conn = getConnexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Redirection selon le rôle
        switch ($user['role']) {
            case 'client':
                header('Location: ../espaces/client/index.php');
                break;
            case 'livreur':
                header('Location: ../espaces/livreur/index.php');
                break;
            case 'commercant':
                header('Location: ../espaces/commercant/index.php');
                break;
            case 'prestataire':
                header('Location: ../espaces/prestataire/index.php');
                break;
            default:
                header('Location: ../features/backend.php');
                break;
        }
        exit;
    } else {
        $error = "Identifiants invalides";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <form class="p-4 bg-white rounded shadow" method="POST">
        <h2 class="mb-4">Connexion</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse Email</label>
            <input type="email" name="email" required class="form-control">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" name="password" required class="form-control">
        </div>

        <button class="btn btn-primary w-100">Connexion</button>
        <a href="register.php" class="d-block text-center mt-3">Créer un compte</a>
    </form>
</body>
</html>