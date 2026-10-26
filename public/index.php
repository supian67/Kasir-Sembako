<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pageTitle = 'Dashboard';
$today = $pdo->query("SELECT COUNT(*) AS transactions, COALESCE(SUM(total),0) AS revenue FROM sales WHERE DATE(created_at)=CURDATE()")->fetch();
$productsCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock <= min_stock")->fetchColumn();
$recent = $pdo->query("SELECT s.invoice_no,s.total,s.created_at,u.name FROM sales s JOIN users u ON u.id=s.user_id ORDER BY s.id DESC LIMIT 8")->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><div><h1>Dashboard</h1><div class="muted">Ringkasan toko hari ini.</div></div><a class="btn" href="<?= BASE_URL ?>/pos.php">＋ Transaksi Baru</a></div>
<div class="grid">
  <div class="card"><div class="stat-label">Penjualan hari ini</div><div class="stat-value"><?= money($today['revenue']) ?></div></div>
  <div class="card"><div class="stat-label">Transaksi hari ini</div><div class="stat-value"><?= (int)$today['transactions'] ?></div></div>
  <div class="card"><div class="stat-label">Jenis produk</div><div class="stat-value"><?= $productsCount ?></div></div>
  <div class="card"><div class="stat-label">Produk stok menipis</div><div class="stat-value <?= $lowStock ? 'low-stock' : '' ?>"><?= $lowStock ?></div></div>
</div>
<div class="card" style="margin-top:18px"><div class="page-head"><h2>Transaksi Terbaru</h2><a href="<?= BASE_URL ?>/history.php" class="btn btn-secondary">Lihat semua</a></div>
<div class="table-wrap"><table><thead><tr><th>No. Invoice</th><th>Tanggal</th><th>Kasir</th><th class="right">Total</th></tr></thead><tbody>
<?php foreach ($recent as $r): ?><tr><td><?= e($r['invoice_no']) ?></td><td><?= e($r['created_at']) ?></td><td><?= e($r['name']) ?></td><td class="right"><?= money($r['total']) ?></td></tr><?php endforeach; ?>
<?php if (!$recent): ?><tr><td colspan="4" class="empty">Belum ada transaksi.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
