<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT s.*,u.name AS cashier FROM sales s JOIN users u ON u.id=s.user_id WHERE s.id=?');$stmt->execute([$id]);$sale=$stmt->fetch();
if(!$sale){http_response_code(404);exit('Struk tidak ditemukan.');}
$itemsStmt=$pdo->prepare('SELECT * FROM sale_items WHERE sale_id=? ORDER BY id');$itemsStmt->execute([$id]);$items=$itemsStmt->fetchAll();
$pageTitle='Struk '.$sale['invoice_no'];require __DIR__.'/../includes/header.php';
?>
<div class="page-head no-print"><div><h1>Struk Pembelian</h1><div class="muted">Transaksi berhasil disimpan.</div></div><div class="actions"><button class="btn" onclick="window.print()">Cetak Struk</button><a class="btn btn-secondary" href="<?=BASE_URL?>/pos.php">Transaksi Baru</a></div></div>
<section class="card receipt"><h2>KASIR SEMBAKO</h2><div style="text-align:center">Struk Pembelian</div><div class="line"></div>
<div>No: <?=e($sale['invoice_no'])?></div><div>Tanggal: <?=e($sale['created_at'])?></div><div>Kasir: <?=e($sale['cashier'])?></div><div class="line"></div>
<table><tbody><?php foreach($items as $it):?><tr><td><?=e($it['product_name'])?><br><?=e($it['qty'])?> <?=e($it['unit'])?> x <?=money($it['price'])?></td><td class="right"><?=money($it['line_total'])?></td></tr><?php endforeach;?></tbody></table>
<div class="line"></div><table><tbody><tr><td>Subtotal</td><td class="right"><?=money($sale['subtotal'])?></td></tr><tr><td>Diskon</td><td class="right"><?=money($sale['discount'])?></td></tr><tr><td><strong>TOTAL</strong></td><td class="right"><strong><?=money($sale['total'])?></strong></td></tr><tr><td>Bayar</td><td class="right"><?=money($sale['paid'])?></td></tr><tr><td>Kembalian</td><td class="right"><?=money($sale['change_amount'])?></td></tr></tbody></table>
<div class="line"></div><div style="text-align:center">Terima kasih telah berbelanja!</div></section>
<?php require __DIR__.'/../includes/footer.php';?>
