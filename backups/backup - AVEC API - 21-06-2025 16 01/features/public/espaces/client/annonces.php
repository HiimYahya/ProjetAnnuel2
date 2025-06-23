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
  <title>Mes annonces</title>
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
        <input type="text" name="adresse_depart" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Adresse d'arrivée</label>
        <input type="text" name="adresse_arrivee" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Taille (en cm, kg...)</label>
        <input type="number" name="taille" class="form-control" min="0" step="any" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Date de livraison souhaitée</label>
        <input type="date" name="date_livraison" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Prix du transport (€)</label>
        <input type="number" name="prix" class="form-control" min="0" step="0.01" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Date d'expiration de l'annonce</label>
        <input type="date" name="date_expiration" class="form-control" required>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" name="segmentation_possible" class="form-check-input" id="segmentation" checked>
        <label class="form-check-label" for="segmentation">Livraison en segments possible (avec points relais)</label>
      </div>
      <button class="btn btn-primary" type="submit">Publier l'annonce</button>
    </form>
    <div id="message-annonce"></div>
    <h2 class="mb-4">Mes annonces</h2>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Titre</th>
          <th>Départ</th>
          <th>Arrivée</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="annonces-list">
        <tr><td colspan="5" class="text-center">Chargement...</td></tr>
      </tbody>
    </table>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../../../assets/js/darkmode.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    function statutCouleur(statut) {
      statut = (statut || '').toLowerCase();
      if (statut === 'livrée') return '🟢';
      if (statut === 'en attente' || statut === 'prise en charge') return '🟠';
      if (statut === 'annulée') return '🔴';
      return '⚪';
    }

    function chargerAnnonces() {
      fetch('../../../../api/client/annonces/get.php', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
          const tbody = document.getElementById('annonces-list');
          tbody.innerHTML = '';

          if (!Array.isArray(data)) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-danger">Erreur de format de données</td></tr>';
            return;
          }

          if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Aucune annonce</td></tr>';
            return;
          }

          data.forEach(annonce => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
              <td>${annonce.titre || ''}</td>
              <td>${annonce.ville_depart || ''}</td>
              <td>${annonce.ville_arrivee || ''}</td>
              <td>${statutCouleur(annonce.statut)} ${annonce.statut || ''}</td>
              <td>
                <a href="livraisons.php?id=${encodeURIComponent(annonce.id)}" class="btn btn-info btn-sm">Suivi</a>
                <button class="btn btn-danger btn-sm">Supprimer</button>
              </td>
            `;

            const btnSupprimer = tr.querySelector('button');
            btnSupprimer.addEventListener('click', function () {
              supprimerAnnonce(annonce.id);
            });

            tbody.appendChild(tr);
          });
        })
        .catch(e => {
          document.getElementById('annonces-list').innerHTML = '<tr><td colspan="5" class="text-danger">Erreur JS : ' + e + '</td></tr>';
        });
    }

    function supprimerAnnonce(id) {
      if (!confirm('Confirmer la suppression de cette annonce ?')) return;

      fetch(`../../../../api/client/annonces/delete.php?id=${encodeURIComponent(id)}`, {
        method: 'DELETE',
        credentials: 'same-origin'
      })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            chargerAnnonces();
          } else {
            alert('Erreur suppression');
          }
        });
    }

    const form = document.getElementById('form-annonce');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        const data = {
          titre: this.titre.value,
          description: this.description.value,
          adresse_depart: this.adresse_depart.value,
          adresse_arrivee: this.adresse_arrivee.value,
          taille: this.taille.value,
          prix: this.prix.value,
          date_livraison: this.date_livraison.value,
          date_expiration: this.date_expiration.value,
          segmentation_possible: this.segmentation_possible.checked
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
                chargerAnnonces();
                form.reset();
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

    chargerAnnonces();
  });
  </script>
  <?php include '../../../../fonctions/footer.php'; ?>
</body>
</html>
