<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
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

//setup Globals
try {
    // create a log channel
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler(__DIR__ . '/../var/logs/site.log', Level::Warning));

    $log->info('Bootstrap file loaded');

    // Load templates from a specific directory
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/templates');

    // Initialize Twig environment with optional caching
    $twig = new \Twig\Environment($loader, [
        'cache' => __DIR__ . '/../var/cache',
    ]);

    // Render a template and pass variables
    echo $twig->render('index.html.twig', ['name' => 'Fabien']);

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

