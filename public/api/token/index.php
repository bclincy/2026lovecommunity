<?php

require_once __DIR__ . '../../../bootstrap.php';

$encypt = new \App\Services\Encryptor();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = createToken();
    header('Access-Control-Allow-Origin: *');
    echo json_encode([
        'code' => 200,
        'status' => 'accepted',
        'msg' => 'Token generated successfully',
        'token' => $token
    ]);
} else {
    echo json_encode([
        'code' => 401,
        'status' => 'rejected',
        'msg' => 'Invalid request please make sure you are request is correctly formated.'
    ]);
}


function createToken($expiry = 3600): string
{
    $newToken = [
        'request_time' => time(),
        'expiry_time' => time() + $expiry,
        'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown',
    ];

    $token = base64_encode(json_encode($newToken));
    return $token;
}