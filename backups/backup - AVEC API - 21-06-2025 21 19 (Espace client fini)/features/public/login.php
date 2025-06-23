<?php

session_start();

define('BASE_URL', '/site_web');

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <form class="p-4 bg-white rounded shadow" id="login-form">
        <h2 class="mb-4">Connexion</h2>
        <div id="login-error"></div>
        <div class="mb-3">
            <label for="email" class="form-label">Adresse Email</label>
            <input type="email" name="email" required class="form-control">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" name="password" required class="form-control">
        </div>
        <button class="btn btn-primary w-100" type="submit">Connexion</button>
        <a href="<?= BASE_URL ?>/features/public/register.php" class="d-block text-center mt-3">Créer un compte</a>
    </form>
    <script>
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.email.value;
        const password = this.password.value;
        fetch('/site_web/api/public/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Envoyer les infos utilisateur à login_session.php pour set la session
                fetch('/site_web/features/public/login_session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(res.user)
                })
                .then(() => {
                    // Rediriger selon le rôle
                    if (res.user.role === 'admin') {
                        window.location.href = '/site_web/features/admin/index.php';
                    } else {
                        window.location.href = '/site_web/features/public/espaces/' + res.user.role + '/index.php';
                    }
                });
            } else {
                document.getElementById('login-error').innerHTML = '<div class="alert alert-danger">' + res.message + '</div>';
            }
        })
        .catch(e => {
            document.getElementById('login-error').innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '</div>';
        });
    });
    </script>
</body>
</html>