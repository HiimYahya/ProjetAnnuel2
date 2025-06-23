<?php

session_start();

$BASE_URL = '/site_web';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <form class="p-4 bg-white rounded shadow" id="register-form">
        <h2 class="mb-4">Créer un compte</h2>
        <div id="register-error"></div>
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
        <button class="btn btn-primary w-100" type="submit">S'inscrire</button>
        <a href="login.php" class="d-block text-center mt-3">Déjà un compte ?</a>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('register-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const nom = this.nom.value;
        const email = this.email.value;
        const mot_de_passe = this.mot_de_passe.value;
        const role = this.role.value;
        fetch('/site_web/api/public/auth/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom, email, mot_de_passe, role })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Envoyer les infos utilisateur à register_session.php pour set la session
                fetch('/site_web/features/public/register_session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(res.user)
                })
                .then(() => {
                    if (res.user.role === 'livreur') {
                        window.location.href = '/site_web/features/public/upload_identite.php';
                    } else {
                        window.location.href = '/site_web/features/public/espaces/' + res.user.role + '/index.php';
                    }
                });
            } else {
                document.getElementById('register-error').innerHTML = '<div class="alert alert-danger">' + res.message + '</div>';
            }
        })
        .catch(e => {
            document.getElementById('register-error').innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '</div>';
        });
    });
    </script>
    <?php include '../../fonctions/darkmode.php'; ?>
</body>
</html>