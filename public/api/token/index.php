<?php

use App\Services\Encryptor;

require_once __DIR__ . '/../../../bootstrap.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = createToken();
    header('Access-Control-Allow-Origin: *');
    echo json_encode([
        'code' => 200,
        'status' => 'accepted',
        'msg' => 'Token generated successfully',
        'token' => $token,
    ]);
} else {
    echo json_encode([
        'code' => 401,
        'status' => 'rejected',
        'msg' => 'Invalid request please make sure you are request is correctly formated.'
    ]);
}

// echo $twig->render('index.html.twig', ['name' => 'Fabien']);



function createToken($expiry = 3600): string
{
    $newToken = [
        'expiry_time' => time() + $expiry,
        'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown',
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];

    $data = json_encode($newToken);

    return Encryptor::encryptStr($data);
}