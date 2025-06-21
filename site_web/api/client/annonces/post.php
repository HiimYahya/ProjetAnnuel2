<?php
session_start();
header('Content-Type: application/json');

// Refuser les requêtes non POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
  exit;
}

// Vérification session
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
  echo json_encode(['success' => false, 'message' => 'Non autorisé']);
  exit;
}

// Lecture du JSON brut
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
  echo json_encode(['success' => false, 'message' => 'Données JSON invalides', 'debug' => $rawInput]);
  exit;
}

// Récupération des champs
$id_client = $_SESSION['utilisateur']['id'];
$titre = $data['titre'] ?? null;
$description = $data['description'] ?? null;
$ville_depart = $data['adresse_depart'] ?? null;
$ville_arrivee = $data['adresse_arrivee'] ?? null;
$taille = $data['taille'] ?? null;
$prix = $data['prix'] ?? null;
$date_livraison = $data['date_livraison'] ?? null;
$date_expiration = $data['date_expiration'] ?? null;
$segmentation_possible = isset($data['segmentation_possible']) ? (int)$data['segmentation_possible'] : 1;
$date_annonce = date('Y-m-d');

// Champs obligatoires
if (!$titre || !$description || !$ville_depart || !$ville_arrivee) {
  echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
  exit;
}

// Connexion à la BDD
require_once __DIR__ . '/../../../fonctions/db.php';
$pdo = getConnexion();

try {
  $stmt = $pdo->prepare("INSERT INTO annonces (id_client, titre, description, ville_depart, ville_arrivee, date_annonce, statut, taille, prix, date_livraison_souhaitee, date_expiration, segmentation_possible)
    VALUES (?, ?, ?, ?, ?, ?, 'en attente', ?, ?, ?, ?, ?)");

  $ok = $stmt->execute([
    $id_client, $titre, $description, $ville_depart, $ville_arrivee,
    $date_annonce, $taille, $prix, $date_livraison, $date_expiration, $segmentation_possible
  ]);

  echo json_encode(['success' => $ok]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()]);
}
