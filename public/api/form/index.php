<?php

require_once __DIR__ . '/../../../bootstrap.php';

header('Content-Type: application/json');

$encypt = new \App\Services\Encryptor();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = json_decode(file_get_contents('php://input'), true);
    if (isset($formData['token'])) {
        $decrypted = $encypt->decryptStr($formData['token']);
        $formdata['meta'] = $decrypted;
        $records = getContactData();
        $records[] = $formData;
        saveContactData($records);
        header('Access-Control-Allow-Origin: *');
        echo json_encode([
            'code' => 200,
            'status' => 'accepted',
            'msg' => 'Token decrypted successfully',
            'data' => $formData['']
        ]);
    } else {
        echo json_encode([
            'code' => 400,
            'status' => 'rejected',
            'msg' => 'Token is missing from the request body.'
        ]);
    }
} else {
    echo json_encode([
        'code' => 401,
        'status' => 'rejected',
        'msg' => 'Invalid request please make sure you are request is correctly formated.'
    ]);
}

// $email = (new Email())
//     ->from('NoReply <' . $_SERVER['MAILFROM'] . '>')
//     ->to('Brian Clincy <bclincy@gmail.com>')
//     ->cc(new Address('bclincy@brianclincy.com', 'Brian Clincy'))
//->cc('cc@example.com')
//->bcc('bcc@example.com')
//->replyTo('fabien@example.com')
//->priority(Email::PRIORITY_HIGH)
// ->subject('Time for Symfony Mailer!')
// ->text('Sending emails is fun again!')
// ->addPart(new DataPart(fopen(__DIR__ . '/img/logo.png', 'r'), 'logo', 'image/png')->asInline())

// ->addPart(new DataPart(fopen(__DIR__ . '/img/logo.png', 'r'), 'logo', 'image/png'))
// ->addPart(new DataPart(new File('/path/to/images/signature.gif'), 'footer-signature', 'image/gif')->asInline())

// reference images using the syntax 'cid:' + "image embed name"
// ->html('<h1>Hello, Symfony Mailer!</h1><img src="cid:logo" width="50" height="50"> ... <img src="cid:footer-signature"> Help me...');
// ->html('<p>See Twig integration for better HTML integration!</p>');

// $mailer->send($email);


function getContactData(): array
{
    $file = __DIR__ . '/../../../data/contacts.json';
    $records = file_exists($file) ? file_get_contents($file) : '[]';

    return json_decode($records, true);

}

function saveContactData(array $data): bool
{
    $file = __DIR__ . '/../../../data/contacts.json';
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}