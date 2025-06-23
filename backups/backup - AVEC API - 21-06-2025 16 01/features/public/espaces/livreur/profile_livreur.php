<?php
session_start();
require_once '../../../../fonctions/db.php';

$conn = getConnexion();

if (!isset($_SESSION['utilisateur'])) {
    header("Location: /site_web/features/public/auth/login.php");
    exit;
}

$id = $_SESSION['utilisateur']['id'];
$role = $_SESSION['utilisateur']['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $photo_profil = null;

    $sql = "UPDATE utilisateurs SET nom = ?, email = ?";
    $params = [$nom, $email];

    if (!empty($mot_de_passe)) {
        $sql .= ", mot_de_passe = ?";
        $params[] = password_hash($mot_de_passe, PASSWORD_BCRYPT);
    }

    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === 0) {
        $upload_dir = '../../../../uploads/';
        $ext = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('photo_') . '.' . $ext;
        $target_path = $upload_dir . $filename;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            move_uploaded_file($_FILES['photo_profil']['tmp_name'], $target_path);
            $sql .= ", photo_profil = ?";
            $params[] = $filename;
        }
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    $_SESSION['utilisateur']['nom'] = $nom;
    $_SESSION['utilisateur']['email'] = $email;

    $success = "Profil mis à jour avec succès.";
}

// Récupération des infos
$stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php include '../../../../fonctions/header_livreur.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <main class="container py-5">
        <h2>Mon profil</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($user['photo_profil'])): ?>
            <div class="mb-4">
                <img src="/site_web/uploads/<?= htmlspecialchars($user['photo_profil']) ?>" class="rounded-circle" width="120" height="120" alt="Photo de profil">
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white shadow-sm p-4 rounded">
            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="mot_de_passe" class="form-control" placeholder="Laisser vide pour ne pas changer">
            </div>

            <div class="mb-3">
                <label class="form-label">Photo de profil</label>
                <input type="file" name="photo_profil" class="form-control">
            </div>

            <button class="btn btn-primary">Mettre à jour</button>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../../assets/js/darkmode.js"></script>
    <?php include '../../../../fonctions/footer.php'; ?>

</body>
</html>
