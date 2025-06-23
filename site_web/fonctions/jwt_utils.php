<?php
// Utilitaire JWT pour API
// Nécessite firebase/php-jwt (composer require firebase/php-jwt)
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

const JWT_SECRET = 'votre_cle_secrete_a_remplacer'; // À personnaliser !
const JWT_ALGO = 'HS256';

function create_jwt($payload) {
    $issuedAt = time();
    $expire = $issuedAt + 60*60*24; // 24h
    $payload['iat'] = $issuedAt;
    $payload['exp'] = $expire;
    return JWT::encode($payload, JWT_SECRET, JWT_ALGO);
}

function verify_jwt($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALGO));
        return (array)$decoded;
    } catch (Exception $e) {
        return false;
    }
} 