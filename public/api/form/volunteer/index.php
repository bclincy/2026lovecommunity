<?php

use App\Services\Encryptor;

require_once __DIR__ . '/../../../../bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = json_decode(file_get_contents('php://input'), true);

    if (!is_array($formData)) {
        echo json_encode([
            'code' => 400,
            'status' => 'rejected',
            'success' => false,
            'message' => 'Invalid request payload.',
        ]);
        exit;
    }

    if (!isset($formData['token'])) {
        echo json_encode([
            'code' => 400,
            'status' => 'rejected',
            'success' => false,
            'message' => 'Token is missing from the request body.',
        ]);
        exit;
    }

    $decrypted = Encryptor::decryptStr($formData['token']);
    if ($decrypted === false) {
        echo json_encode([
            'code' => 400,
            'status' => 'rejected',
            'success' => false,
            'message' => 'Invalid or expired token.',
        ]);
        exit;
    }
    $meta = json_decode((string) $decrypted, true);
    if (!is_array($meta) || !isTokenValid($meta)) {
        echo json_encode([
            'code' => 400,
            'status' => 'rejected',
            'success' => false,
            'message' => 'Invalid or expired token.',
        ]);
        exit;
    }

    $formData['meta'] = $meta;
    $formData['received_at'] = time();
    if (!isset($formData['processed'])) {
        $formData['processed'] = false;
    }
    $formData['form_type'] = 'volunteer';

    $records = getVolunteerData();
    $records[] = $formData;

    if (saveVolunteerData($records)) {
        header('Access-Control-Allow-Origin: *');
        echo json_encode([
            'code' => 200,
            'status' => 'accepted',
            'success' => true,
            'message' => 'Volunteer submission saved successfully.',
        ]);
        exit;
    }

    echo json_encode([
        'code' => 500,
        'status' => 'rejected',
        'success' => false,
        'message' => 'Unable to save volunteer submission.',
    ]);
    exit;
}

echo json_encode([
    'code' => 401,
    'status' => 'rejected',
    'success' => false,
    'message' => 'Only POST requests are accepted for this endpoint.',
]);

function getVolunteerData(): array
{
    $file = __DIR__ . '/../../../../data/volunteers.json';
    $contents = file_exists($file) ? file_get_contents($file) : '[]';
    $records = json_decode($contents, true);

    return is_array($records) ? $records : [];
}

function saveVolunteerData(array $data): bool
{
    $file = __DIR__ . '/../../../../data/volunteers.json';
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function isTokenValid(array $meta): bool
{
    if (isset($meta['expiry_time'])) {
        return time() <= intval($meta['expiry_time']);
    }

    if (isset($meta['timestamp'])) {
        return !expiredRequest(intval($meta['timestamp']));
    }

    return false;
}

function expiredRequest(int $timestamp): bool
{
    return (time() - $timestamp) > 300;
}
