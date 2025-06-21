<?php
// Vérifier si la session existe
if (!isset($_SESSION)) {
    session_start();
}

// Rediriger si non authentifié ou non admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    header('Location: /site_web/features/public/login.php');
    exit;
}
?>

<header class="admin-navbar">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="/site_web/features/admin/index.php">
        <strong>EcoDeli Admin</strong>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="/site_web/features/admin/index.php">
              <svg class="bi" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
              Tableau de bord
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/site_web/features/admin/utilisateurs.php">
              <svg class="bi" width="16" height="16"><use xlink:href="#people-circle"/></svg>
              Utilisateurs
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/site_web/features/admin/annonces.php">
              <svg class="bi" width="16" height="16"><use xlink:href="#megaphone"/></svg>
              Annonces
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/site_web/features/admin/livraisons.php">
              <svg class="bi" width="16" height="16"><use xlink:href="#truck"/></svg>
              Livraisons
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/site_web/features/admin/paiements.php">
              <svg class="bi" width="16" height="16"><use xlink:href="#cash-coin"/></svg>
              Paiements
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/site_web/features/admin/livreurs.php">
              <svg class="bi" width="16" height="16"><use xlink:href="#id-card"/></svg>
              Validation Livreurs
            </a>
          </li>
        </ul>
        
        <div class="d-flex align-items-center">
          <div class="dropdown me-3">
            <button class="btn btn-sm btn-outline-light position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <svg class="bi" width="16" height="16"><use xlink:href="#bell"/></svg>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">2</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><h6 class="dropdown-header">Notifications</h6></li>
              <li><a class="dropdown-item" href="#">2 nouvelles livraisons</a></li>
              <li><a class="dropdown-item" href="#">5 nouveaux utilisateurs</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">Voir toutes les notifications</a></li>
            </ul>
          </div>
          
          <div class="dropdown me-3">
            <button class="btn btn-sm btn-outline-light d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?php if (!empty($_SESSION['utilisateur']['photo_profil'])): ?>
                <img src="/site_web/uploads/<?php echo htmlspecialchars($_SESSION['utilisateur']['photo_profil']); ?>" alt="Photo de profil" class="rounded-circle me-2" width="24" height="24">
              <?php else: ?>
                <span class="rounded-circle bg-light text-primary me-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 12px;">
                  <?php echo substr(htmlspecialchars($_SESSION['utilisateur']['nom'] ?? 'A'), 0, 1); ?>
                </span>
              <?php endif; ?>
              <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['utilisateur']['nom'] ?? 'Admin'); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><h6 class="dropdown-header">Mon compte</h6></li>
              <li>
                <a class="dropdown-item" href="/site_web/features/admin/profil.php">
                  <svg class="bi" width="16" height="16"><use xlink:href="#person"/></svg> Profil
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="/site_web/features/admin/configuration.php">
                  <svg class="bi" width="16" height="16"><use xlink:href="#gear"/></svg> Paramètres
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="/site_web/features/public/logout.php">
                  <svg class="bi" width="16" height="16"><use xlink:href="#box-arrow-right"/></svg> Déconnexion
                </a>
              </li>
            </ul>
          </div>
          
          <button id="themeToggle" class="btn btn-sm btn-outline-light">
            <svg class="bi dark-icon" width="16" height="16"><use xlink:href="#moon-stars-fill"/></svg>
            <svg class="bi light-icon d-none" width="16" height="16"><use xlink:href="#sun-fill"/></svg>
          </button>
        </div>
      </div>
    </div>
  </nav>
</header>



<style>
:root {
  --navbar-height: 48px;
  --primary-color: #4e73df;
  --secondary-color: #858796;
  --success-color: #1cc88a;
  --info-color: #36b9cc;
  --warning-color: #f6c23e;
  --danger-color: #e74a3b;
  --light-color: #f8f9fc;
  --dark-color: #5a5c69;
  --body-bg: #f8f9fc;
}

[data-bs-theme="dark"] {
  --body-bg: #1e1e2d;
  --light-color: #2b2b40;
}

body {
  padding-top: var(--navbar-height);
  background-color: var(--body-bg);
  margin: 0;
  font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.admin-navbar .navbar {
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
  height: var(--navbar-height);
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}

.admin-navbar .nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0.35rem 0.75rem;
}

.admin-navbar .nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 0.25rem;
}

.admin-navbar .nav-link.active {
  font-weight: 600;
  background-color: rgba(255, 255, 255, 0.15);
  border-radius: 0.25rem;
}

.admin-content-wrapper {
  min-height: calc(100vh - var(--navbar-height));
}

.admin-page-title {
  margin-top: calc(var(--navbar-height) + 0.25rem) !important;
  padding-top: 0.25rem !important;
  padding-bottom: 0.25rem !important;
  margin-bottom: 0.5rem !important;
}

/* Styles pour le thème sombre */
[data-bs-theme="dark"] .admin-page-title {
  background-color: #1a1a27 !important;
  border-color: #2b2b40 !important;
}

[data-bs-theme="dark"] .page-title {
  color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Définir le titre de la page en fonction de la page actuelle
  const pageTitle = document.querySelector('.page-title');
  const currentPage = window.location.pathname.split('/').pop().split('.')[0];
  
  // Marquer l'élément de navigation actif
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (href && href.includes(currentPage)) {
      link.classList.add('active');
    }
  });
  
  // Définir le titre de la page
  switch(currentPage) {
    case 'index':
      pageTitle.textContent = 'Tableau de bord';
      break;
    case 'utilisateurs':
      pageTitle.textContent = 'Gestion des utilisateurs';
      break;
    case 'annonces':
      pageTitle.textContent = 'Gestion des annonces';
      break;
    case 'livraisons':
      pageTitle.textContent = 'Gestion des livraisons';
      break;
    case 'paiements':
      pageTitle.textContent = 'Gestion des paiements';
      break;
    case 'statistiques':
      pageTitle.textContent = 'Statistiques';
      break;
    default:
      pageTitle.textContent = 'Administration';
  }
  
  // Gestion du thème
  const themeToggle = document.getElementById('themeToggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const currentTheme = document.documentElement.getAttribute('data-bs-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-bs-theme', newTheme);
      localStorage.setItem('adminTheme', newTheme);
      
      // Mise à jour des icônes
      const darkIcon = document.querySelector('.dark-icon');
      const lightIcon = document.querySelector('.light-icon');
      if (darkIcon && lightIcon) {
        if (newTheme === 'dark') {
          darkIcon.classList.add('d-none');
          lightIcon.classList.remove('d-none');
        } else {
          darkIcon.classList.remove('d-none');
          lightIcon.classList.add('d-none');
        }
      }
    });
  }
  
  // Appliquer le thème sauvegardé
  const savedTheme = localStorage.getItem('adminTheme');
  if (savedTheme) {
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    
    const darkIcon = document.querySelector('.dark-icon');
    const lightIcon = document.querySelector('.light-icon');
    if (darkIcon && lightIcon) {
      if (savedTheme === 'dark') {
        darkIcon.classList.add('d-none');
        lightIcon.classList.remove('d-none');
      } else {
        darkIcon.classList.remove('d-none');
        lightIcon.classList.add('d-none');
      }
    }
  }
});
</script>

<?php include_once __DIR__ . '/icons.php'; ?> 

<!-- Symboles SVG pour les icônes -->
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  <symbol id="speedometer2" viewBox="0 0 16 16">
    <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
    <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
  </symbol>
  <symbol id="people-circle" viewBox="0 0 16 16">
    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
  </symbol>
  <symbol id="megaphone" viewBox="0 0 16 16">
    <path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0v-11zm-1 .724c-2.067.95-4.539 1.481-7 1.656v6.237a25.222 25.222 0 0 1 1.088.085c2.053.204 4.038.668 5.912 1.56V3.224zm-8 7.841V4.934c-.68.027-1.399.043-2.008.053A2.02 2.02 0 0 0 0 7v2c0 1.106.896 1.996 1.994 2.009a68.14 68.14 0 0 1 .496.008 64 64 0 0 1 1.51.048zm1.39 1.081c.285.021.569.047.85.078l.253 1.69a1 1 0 0 1-.983 1.187h-.548a1 1 0 0 1-.916-.599l-1.314-2.48a65.81 65.81 0 0 1 1.692.064c.327.017.65.037.966.06z"/>
  </symbol>
  <symbol id="truck" viewBox="0 0 16 16">
    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 1 1 1 1.732V6.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5V10z"/>
  </symbol>
  <symbol id="cash-coin" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0z"/>
    <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1h-.003zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195l.054.012z"/>
    <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083c.058-.344.145-.678.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1H1z"/>
    <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 5.982 5.982 0 0 1 3.13-1.567z"/>
  </symbol>
  <symbol id="id-card" viewBox="0 0 16 16">
    <path d="M6 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0M1 7.086a1 1 0 0 0 .293.707L3.75 10.25l.043.043a1 1 0 0 0 1.414 0A1 1 0 0 0 4.5 9a.5.5 0 0 1 0-1 1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v1.086z"/>
    <path d="M13.5 3a.5.5 0 0 1 .5.5V5h-1.5A1.5 1.5 0 0 0 11 6.5v5A1.5 1.5 0 0 0 12.5 13H14a.5.5 0 0 1 0 1h-1.5A2.5 2.5 0 0 1 10 11.5v-5A2.5 2.5 0 0 1 12.5 4H14a.5.5 0 0 1 0 1h-1.5A1.5 1.5 0 0 0 11 6.5V8h1.5a.5.5 0 0 1 0 1H11v1.5a.5.5 0 0 1-1 0V1.5A.5.5 0 0 1 10.5 1H8a.5.5 0 0 1 0-1h2.5a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 13.5 3"/>
  </symbol>
  <symbol id="bell" viewBox="0 0 16 16">
    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z"/>
  </symbol>
  <symbol id="person" viewBox="0 0 16 16">
    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664h10z"/>
  </symbol>
  <symbol id="gear" viewBox="0 0 16 16">
    <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
    <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
  </symbol>
  <symbol id="box-arrow-right" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
  </symbol>
  <symbol id="moon-stars-fill" viewBox="0 0 16 16">
    <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
    <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
  </symbol>
  <symbol id="sun-fill" viewBox="0 0 16 16">
    <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
  </symbol>
</svg> 