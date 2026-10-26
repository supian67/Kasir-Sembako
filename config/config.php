<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

define('APP_NAME', 'Kasir Sembako');
define('BASE_URL', '/kasir-sembako-mysql/public'); // Sesuaikan jika nama folder/URL berbeda.

$dbHost = '127.0.0.1';
$dbName = 'kasir_sembako';
$dbUser = 'root';
$dbPass = ''; // Isi jika MySQL Anda menggunakan password.

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa MySQL dan pengaturan config/config.php.');
}
