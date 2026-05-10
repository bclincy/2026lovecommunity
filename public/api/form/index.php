<?php

require_once __DIR__ . '/../../../bootstrap.php';

header('Content-Type: application/json');

$encypt = new \App\Services\Encryptor();
die(var_dump($_REQUEST));


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