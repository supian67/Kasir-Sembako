<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= BASE_URL ?>/index.php">🛒 Kasir Sembako</a>
  <?php if ($user): ?>
  <div class="userbox"><span><?= e($user['name']) ?> · <?= e(strtoupper($user['role'])) ?></span>
    <form action="<?= BASE_URL ?>/logout.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <button class="btn btn-light" type="submit">Keluar</button>
    </form>
  </div>
  <?php endif; ?>
</header>
<?php if ($user): ?>
<nav class="nav">
  <a href="<?= BASE_URL ?>/index.php">Dashboard</a>
  <a href="<?= BASE_URL ?>/pos.php">Kasir / Penjualan</a>
  <a href="<?= BASE_URL ?>/products.php">Stok & Produk</a>
  <a href="<?= BASE_URL ?>/history.php">Riwayat Transaksi</a>
  <a href="<?= BASE_URL ?>/reports.php">Laporan</a>
  <?php if ($user['role'] === 'admin'): ?><a href="<?= BASE_URL ?>/users.php">Pengguna</a><?php endif; ?>
</nav>
<?php endif; ?>
<main class="container">
