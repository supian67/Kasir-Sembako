<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();
header('Content-Type: application/json; charset=utf-8');
$q = trim((string)($_GET['q'] ?? ''));
if ($q === '') {
    $stmt = $pdo->query('SELECT id,barcode,name,unit,sell_price,stock FROM products WHERE stock > 0 ORDER BY name LIMIT 100');
} else {
    $stmt = $pdo->prepare('SELECT id,barcode,name,unit,sell_price,stock FROM products WHERE stock > 0 AND (barcode LIKE ? OR name LIKE ?) ORDER BY name LIMIT 100');
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
}
echo json_encode(['products'=>$stmt->fetchAll()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
