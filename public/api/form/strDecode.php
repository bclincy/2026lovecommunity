<?php

use App\Services\Encryptor;

require_once __DIR__ . '../../../bootstrap.php';


if ($_REQUEST['token'] ?? false) {
    $token = $_REQUEST['token'];
    $decodedToken = Encryptor::decryptStr($token);
    $tokenData = json_decode((string) $decodedToken, true);
    if ($tokenData && isset($tokenData['expiry_time']) && time() < $tokenData['expiry_time']) {
        echo json_encode([
            'code' => 200,
            'status' => 'accepted',
            'msg' => 'Token is valid',
            'data' => $tokenData
        ]);
    } else {
        echo json_encode([
            'code' => 401,
            'status' => 'rejected',
            'msg' => 'Token is invalid or expired'
        ]);
    }
} else {
    echo json_encode([
        'code' => 400,
        'status' => 'rejected',
        'msg' => 'No token provided'
    ]);
}

