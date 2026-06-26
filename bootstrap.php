<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

if (class_exists('App\Services\Encryptor') !== true) {
    require_once __DIR__ . '/src/services/Encryptor.php';
}

//setup Globals
try {
    // create a log channel
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler(__DIR__ . '/var/logs/site.log', Level::Warning));
    $log->info('Bootstrap file loaded');

    // Load templates from a specific directory
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/src/templates');

    // Initialize Twig environment with optional caching
    $twig = new \Twig\Environment($loader, [
        'cache' => __DIR__ . '/var/cache',
        'debug' => true,
    ]);
    $charset = 'utf8mb4';

    // try {
    //     $db = new PDO("mysql:host=localhost;dbname={$_ENV['}", $user, $password);
    //     echo "<h2>TODO</h2><ol>";
    //     foreach ($db->query("SELECT content FROM $table") as $row) {
    //         echo "<li>" . $row['content'] . "</li>";
    //     }
    //     echo "</ol>";
    // } catch (PDOException $e) {
    //     print "Error!: " . $e->getMessage() . "<br/>";
    //     die();
    // }

    // $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=' . $charset;
    // $options = [
    //     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //     PDO::ATTR_EMULATE_PREPARES => false,
    // ];

    $dsnSmtp = 'smtp://' . urlencode($_SERVER['MAILUSER']) . ':' . urlencode($_SERVER['MAILPASS']) . '@' . $_SERVER['MAILHOST'] . ':' . $_SERVER['MAILPORT'] . '?encryption=ssl&require_tls=true';
    $transport = Transport::fromDsn($dsnSmtp, null, null, $log);
    $mailer = new Mailer($transport);

} catch (\Throwable $th) {
    //throw $th;
    $log->error('Error in bootstrap: ' . $th->getMessage());
    $log->error('Stack trace: ' . $th->getTraceAsString());
    header('HTTP/1.1 500 Internal Server Error');
    echo 'An error occurred while loading the application. Please contact the administrator.';
    exit();
}


function sendemail(mailer $mailer)
{
    $email = (new Email())
        ->from('NoReply <' . $_SERVER['MAILFROM'] . '>')
        ->to('Brian Clincy <bclincy@gmail.com>')
        ->cc(new Address('bclincy@brianclincy.com', 'Brian Clincy'))
        ->cc('cc@example.com')
        ->bcc('bcc@example.com')
        ->replyTo('fabien@example.com')
        ->priority(Email::PRIORITY_HIGH)
        ->subject('Time for Symfony Mailer!')
        ->text('Sending emails is fun again!')
        ->addPart(new DataPart(fopen(__DIR__ . '/img/logo.png', 'r'), 'logo', 'image/png'))
        ->addPart(new DataPart(new File('/path/to/images/signature.gif'), 'footer-signature', 'image/gif'))

        // reference images using the syntax 'cid:' + "image embed name"
        ->html('<h1>Hello, Symfony Mailer!</h1><img src="cid:logo" width="50" height="50"> ... <img src="cid:footer-signature"> Help me...');

    $mailer->send($email);
}

