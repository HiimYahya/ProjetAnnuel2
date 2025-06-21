<?php
header('Content-Type: application/json');
require_once '../../../fonctions/db.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['success'=>false,'message'=>'Données manquantes']); exit; }
$nom = $data['nom'] ?? '';
$email = $data['email'] ?? '';
$role = $data['role'] ?? '';
$mot_de_passe = password_hash($data['mot_de_passe'] ?? '', PASSWORD_BCRYPT);
$conn = getConnexion();
$stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success'=>false, 'message'=>'Un compte existe déjà avec cet email.']);
    exit;
}
$validation_identite = ($role === 'livreur') ? 'en_attente' : NULL;
$stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, validation_identite) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$nom, $email, $mot_de_passe, $role, $validation_identite]);
$id = $conn->lastInsertId();
echo json_encode(['success'=>true, 'user'=>['id'=>$id, 'nom'=>$nom, 'email'=>$email, 'role'=>$role, 'validation_identite'=>$validation_identite]]); 