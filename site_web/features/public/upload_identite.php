<?php
session_start();
require_once '../../fonctions/db.php';
require_once '../../fonctions/fonctions.php';

$BASE_URL = '/site_web';

// Vérifier si l'utilisateur est connecté et est un livreur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    header("Location: $BASE_URL/features/public/login.php");
    exit;
}

$conn = getConnexion();
$message = '';
$erreur = '';

// Si l'utilisateur est déjà validé, le rediriger
if (isset($_SESSION['utilisateur']['validation_identite']) && $_SESSION['utilisateur']['validation_identite'] === 'validee') {
    header("Location: $BASE_URL/features/public/espaces/livreur/index.php");
    exit;
}

// Traitement du formulaire de téléchargement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du fichier
    if (isset($_FILES['piece_identite']) && $_FILES['piece_identite']['error'] === UPLOAD_ERR_OK) {
        $fichier = $_FILES['piece_identite'];
        $types_autorises = ['image/jpeg', 'image/png', 'application/pdf'];
        $taille_max = 5 * 1024 * 1024; // 5MB
        
        // Vérifier le type MIME
        if (!in_array($fichier['type'], $types_autorises)) {
            $erreur = "Type de fichier non autorisé. Veuillez télécharger une image (JPG, PNG) ou un PDF.";
        } 
        // Vérifier la taille
        elseif ($fichier['size'] > $taille_max) {
            $erreur = "Le fichier est trop volumineux. Taille maximale: 5MB.";
        } 
        else {
            // Créer un nom de fichier unique
            $extension = pathinfo($fichier['name'], PATHINFO_EXTENSION);
            $nom_fichier = 'identite_' . $_SESSION['utilisateur']['id'] . '_' . uniqid() . '.' . $extension;
            $chemin_destination = '../../uploads/' . $nom_fichier;
            
            // Déplacer le fichier téléchargé
            if (move_uploaded_file($fichier['tmp_name'], $chemin_destination)) {
                // Mettre à jour la base de données
                $stmt = $conn->prepare("UPDATE utilisateurs SET piece_identite = ? WHERE id = ?");
                $stmt->execute([$nom_fichier, $_SESSION['utilisateur']['id']]);
                
                $message = "Votre pièce d'identité a été téléchargée avec succès. Votre compte sera examiné par notre équipe. Vous recevrez un email lorsque votre compte sera validé.";
                
                // Mettre à jour la session
                $_SESSION['utilisateur']['piece_identite'] = $nom_fichier;
            } else {
                $erreur = "Une erreur est survenue lors du téléchargement du fichier.";
            }
        }
    } else {
        $erreur = "Veuillez sélectionner un fichier.";
    }
}

// Vérifier si l'utilisateur a déjà téléchargé une pièce d'identité
$stmt = $conn->prepare("SELECT piece_identite, validation_identite FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['utilisateur']['id']]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

$deja_telecharge = !empty($utilisateur['piece_identite']);
$statut_validation = $utilisateur['validation_identite'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation d'identité - EcoDeli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Validation de votre compte Livreur</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($erreur): ?>
                            <div class="alert alert-danger"><?= $erreur ?></div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= $message ?></div>
                        <?php endif; ?>
                        
                        <?php if ($deja_telecharge): ?>
                            <div class="alert alert-info">
                                <h4><i class="fas fa-info-circle"></i> Votre document a été téléchargé</h4>
                                <p>Statut de validation: 
                                    <?php 
                                        switch($statut_validation) {
                                            case 'en_attente':
                                                echo '<span class="badge bg-warning">En attente de validation</span>';
                                                break;
                                            case 'validee':
                                                echo '<span class="badge bg-success">Validé</span>';
                                                break;
                                            case 'refusee':
                                                echo '<span class="badge bg-danger">Refusé</span>';
                                                break;
                                        }
                                    ?>
                                </p>
                                <?php if ($statut_validation === 'en_attente'): ?>
                                    <p>Votre document est en cours d'examen. Vous recevrez un email lorsque votre compte sera validé.</p>
                                <?php elseif ($statut_validation === 'refusee'): ?>
                                    <p>Votre document a été refusé. Veuillez télécharger un nouveau document.</p>
                                    <form method="POST" enctype="multipart/form-data" class="mt-4">
                                        <div class="mb-3">
                                            <label for="piece_identite" class="form-label">Nouvelle pièce d'identité (JPG, PNG ou PDF, max 5MB)</label>
                                            <input type="file" class="form-control" id="piece_identite" name="piece_identite" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Télécharger</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <h4><i class="fas fa-id-card"></i> Téléchargez votre pièce d'identité</h4>
                                <p>Pour valider votre compte livreur, nous avons besoin d'une pièce d'identité valide (carte d'identité, passeport, permis de conduire).</p>
                                <p>Votre document sera examiné par notre équipe et votre compte sera activé une fois la vérification terminée.</p>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="piece_identite" class="form-label">Pièce d'identité (JPG, PNG ou PDF, max 5MB)</label>
                                    <input type="file" class="form-control" id="piece_identite" name="piece_identite" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Télécharger</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?= $BASE_URL ?>/features/public/logout.php" class="btn btn-secondary">Se déconnecter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 