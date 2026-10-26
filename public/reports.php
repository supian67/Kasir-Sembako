<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$from=(string)($_GET['from']??date('Y-m-01'));$to=(string)($_GET['to']??date('Y-m-d'));
$stmt=$pdo->prepare('SELECT COUNT(*) transactions,COALESCE(SUM(total),0) revenue,COALESCE(SUM(discount),0) discounts FROM sales WHERE DATE(created_at) BETWEEN ? AND ?');$stmt->execute([$from,$to]);$summary=$stmt->fetch();
$stmt=$pdo->prepare('SELECT si.product_name,SUM(si.qty) qty,SUM(si.line_total) revenue,SUM((si.price-si.cost_price)*si.qty) gross_profit FROM sale_items si JOIN sales s ON s.id=si.sale_id WHERE DATE(s.created_at) BETWEEN ? AND ? GROUP BY si.product_id,si.product_name ORDER BY qty DESC LIMIT 20');$stmt->execute([$from,$to]);$top=$stmt->fetchAll();
$pageTitle='Laporan Penjualan';require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h1>Laporan Penjualan</h1><div class="muted">Ringkasan penjualan dan produk terlaris.</div></div><button class="btn btn-secondary no-print" onclick="window.print()">Cetak Laporan</button></div>
<div class="card" style="margin-bottom:16px"><form class="searchbar no-print" method="get"><div class="field"><label>Dari tanggal</label><input type="date" name="from" value="<?=e($from)?>"></div><div class="field"><label>Sampai tanggal</label><input type="date" name="to" value="<?=e($to)?>"></div><button class="btn" style="align-self:end">Tampilkan</button></form>
<div class="grid"><div class="card"><div class="stat-label">Total omzet</div><div class="stat-value"><?=money($summary['revenue'])?></div></div><div class="card"><div class="stat-label">Jumlah transaksi</div><div class="stat-value"><?= (int)$summary['transactions']?></div></div><div class="card"><div class="stat-label">Total diskon</div><div class="stat-value"><?=money($summary['discounts'])?></div></div></div></div>
<div class="card"><h2>Produk Terlaris</h2><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Qty terjual</th><th>Omzet</th><th>Estimasi laba kotor*</th></tr></thead><tbody><?php foreach($top as $r):?><tr><td><?=e($r['product_name'])?></td><td><?=e($r['qty'])?></td><td><?=money($r['revenue'])?></td><td><?=money($r['gross_profit'])?></td></tr><?php endforeach;?><?php if(!$top):?><tr><td colspan="4" class="empty">Belum ada data penjualan.</td></tr><?php endif;?></tbody></table></div><p class="small muted">*Estimasi laba kotor memakai harga beli yang tersimpan pada saat transaksi. Belum termasuk biaya operasional, pajak, dan biaya lain.</p></div>
<?php require __DIR__.'/../includes/footer.php';?>
