<?php

namespace App\Services;
require_once __DIR__ . '../../../bootstrap.php';

use Monolog\Logger;


/**
 * Utility functions for the application
 * @author Brian Clincy
 */

class Utility
{

    public Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $str
     * @return string
     */
    public static function sanitizeString(string $str): string
    {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getTokenData(string $token): ?array
    {
        $decodedToken = Encryptor::decryptStr($token);

        return json_decode((string) $decodedToken, true) ?: null;

    }

}