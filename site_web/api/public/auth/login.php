<?php
header('Content-Type: application/json');
require_once '../../../fonctions/db.php';
require_once '../../../fonctions/jwt_utils.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['success'=>false,'message'=>'Données manquantes']); exit; }
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$conn = getConnexion();
$stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user && password_verify($password, $user['mot_de_passe'])) {
    unset($user['mot_de_passe']);
    $token = create_jwt([
        'id' => $user['id'],
        'nom' => $user['nom'],
        'email' => $user['email'],
        'role' => $user['role'],
        'validation_identite' => $user['validation_identite'] ?? null
    ]);
    echo json_encode(['success'=>true, 'user'=>$user, 'token'=>$token, 'message'=>'Connexion via token réussie']);
} else {
    echo json_encode(['success'=>false, 'message'=>'Identifiants invalides']);
} 