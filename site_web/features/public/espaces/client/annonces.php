<?php
session_start();
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
  header('Location: ../../../../public/login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Créer une annonce</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../../../../fonctions/header_client.php'; ?>
<body class="d-flex flex-column min-vh-100">
  <div class="container py-5">
    <h2 class="mb-4">Créer une annonce</h2>
    <form id="form-annonce" class="mb-5">
      <div class="mb-3">
        <label class="form-label">Titre</label>
        <input type="text" name="titre" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Adresse de départ</label>
        <input type="text" name="ville_depart" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Adresse d'arrivée</label>
        <input type="text" name="ville_arrivee" class="form-control" required>
      </div>
      <div class="row mb-3">
        <div class="col">
          <label class="form-label">Hauteur (cm)</label>
          <input type="number" name="hauteur" class="form-control" min="0" step="any" required>
        </div>
        <div class="col">
          <label class="form-label">Longueur (cm)</label>
          <input type="number" name="longueur" class="form-control" min="0" step="any" required>
        </div>
        <div class="col">
          <label class="form-label">Largeur (cm)</label>
          <input type="number" name="largeur" class="form-control" min="0" step="any" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Date de livraison souhaitée</label>
        <input type="date" name="date_livraison_souhaitee" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Prix de la livraison (€)</label>
        <input type="number" name="prix" class="form-control" min="0" step="0.01" required>
      </div>
      <button class="btn btn-primary" type="submit">Publier l'annonce</button>
    </form>
    <div id="message-annonce"></div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-annonce');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const data = {
          titre: this.titre.value,
          ville_depart: this.ville_depart.value,
          ville_arrivee: this.ville_arrivee.value,
          hauteur: this.hauteur.value,
          longueur: this.longueur.value,
          largeur: this.largeur.value,
          date_livraison_souhaitee: this.date_livraison_souhaitee.value,
          description: this.description.value,
          prix: this.prix.value
        };
        fetch('../../../../api/client/annonces/post.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify(data)
        })
          .then(r => r.text())
          .then(text => {
            try {
              const res = JSON.parse(text);
              if (res.success) {
                form.reset();
                document.getElementById('message-annonce').innerHTML = '<div class="alert alert-success">Annonce créée avec succès !</div>';
              } else {
                document.getElementById('message-annonce').innerHTML = '<div class="alert alert-danger">Erreur : ' + res.message + '</div>';
              }
            } catch (e) {
              document.getElementById('message-annonce').innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '<br>Réponse brute : <pre>' + text + '</pre></div>';
            }
          })
          .catch(e => {
            document.getElementById('message-annonce').innerHTML = '<div class="alert alert-danger">Erreur JS : ' + e + '</div>';
          });
      });
    }
  });
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
