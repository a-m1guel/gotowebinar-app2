<?php
// db.php - Database connection setup

$host = '127.0.0.1'; // Use 127.0.0.1 instead of 'localhost' to avoid potential DNS issues
$db   = 'webinarapp'; // Corrected database name
$user = 'root';        // Default WAMP username
$pass = '';            // Default WAMP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In a real application, you would log this error and show a generic message.
    // For this assessment, it's okay to show the error directly for easier debugging.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>

