<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$from=(string)($_GET['from']??date('Y-m-01'));$to=(string)($_GET['to']??date('Y-m-d'));$q=trim((string)($_GET['q']??''));
$sql='SELECT s.id,s.invoice_no,s.created_at,s.total,s.paid,s.change_amount,u.name AS cashier FROM sales s JOIN users u ON u.id=s.user_id WHERE DATE(s.created_at) BETWEEN ? AND ?';$params=[$from,$to];
if($q!==''){$sql.=' AND (s.invoice_no LIKE ? OR u.name LIKE ?)';$params[]='%'.$q.'%';$params[]='%'.$q.'%';}
$sql.=' ORDER BY s.id DESC LIMIT 500';$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
$pageTitle='Riwayat Transaksi';require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h1>Riwayat Transaksi</h1><div class="muted">Maksimal 500 transaksi sesuai filter.</div></div></div>
<div class="card"><form class="searchbar" method="get"><div class="field"><label>Dari tanggal</label><input type="date" name="from" value="<?=e($from)?>"></div><div class="field"><label>Sampai tanggal</label><input type="date" name="to" value="<?=e($to)?>"></div><div class="field"><label>Invoice/kasir</label><input name="q" value="<?=e($q)?>" placeholder="Cari..."></div><button class="btn" style="align-self:end">Filter</button></form>
<div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Total</th><th>Bayar</th><th>Kembalian</th><th></th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?=e($r['invoice_no'])?></td><td><?=e($r['created_at'])?></td><td><?=e($r['cashier'])?></td><td><?=money($r['total'])?></td><td><?=money($r['paid'])?></td><td><?=money($r['change_amount'])?></td><td><a class="btn btn-secondary" href="<?=BASE_URL?>/receipt.php?id=<?=$r['id']?>">Struk</a></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="7" class="empty">Tidak ada transaksi pada rentang ini.</td></tr><?php endif;?>
</tbody></table></div></div>
<?php require __DIR__.'/../includes/footer.php';?>
