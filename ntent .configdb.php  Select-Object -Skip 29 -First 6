<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

function getDatabase(): PDO
{
    $host = $_ENV['DB_HOST'] ?? '';
    $port = $_ENV['DB_PORT'] ?? '';
    $name = $_ENV['DB_NAME'] ?? '';
    $user = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    if (!$host || !$port || !$name || !$user || !$password) {
        throw new Exception('Configuration de la base de données manquante.');
    }

    $caPath = dirname(__DIR__) . '/certs/ca.pem';

    if (!file_exists($caPath)) {
        throw new Exception('Certificat SSL introuvable.');
    }

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        \Pdo\Mysql::ATTR_SSL_CA => $caPath,
\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT => true
    ];

    return new PDO($dsn, $user, $password, $options);
}
