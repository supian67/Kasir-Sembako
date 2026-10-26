<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$message='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 try {
   $items=json_decode((string)($_POST['items_json']??''),true,512,JSON_THROW_ON_ERROR);
   $discount=max(0,(float)($_POST['discount']??0));$paid=max(0,(float)($_POST['paid']??0));
   if(!is_array($items)||!count($items))throw new RuntimeException('Keranjang kosong.');
   $pdo->beginTransaction();$prepared=[];$subtotal=0;
   $lock=$pdo->prepare('SELECT id,barcode,name,unit,buy_price,sell_price,stock FROM products WHERE id=? FOR UPDATE');
   foreach($items as $item){
     $id=(int)($item['id']??0);$qty=(float)($item['qty']??0);
     if($id<1||$qty<=0)throw new RuntimeException('Item transaksi tidak valid.');
     $lock->execute([$id]);$p=$lock->fetch();
     if(!$p)throw new RuntimeException('Produk tidak ditemukan.');
     if($qty>(float)$p['stock'])throw new RuntimeException('Stok tidak cukup untuk: '.$p['name']);
     $line=$qty*(float)$p['sell_price'];$subtotal+=$line;
     $prepared[]=['product'=>$p,'qty'=>$qty,'line'=>$line];
   }
   $discount=min($discount,$subtotal);$total=$subtotal-$discount;
   if($paid<$total)throw new RuntimeException('Uang pembayaran belum cukup.');
   $invoice='INV'.date('YmdHis').str_pad((string)random_int(0,999),3,'0',STR_PAD_LEFT);
   $stmt=$pdo->prepare('INSERT INTO sales(invoice_no,user_id,subtotal,discount,total,paid,change_amount) VALUES(?,?,?,?,?,?,?)');
   $stmt->execute([$invoice,current_user()['id'],$subtotal,$discount,$total,$paid,$paid-$total]);$saleId=(int)$pdo->lastInsertId();
   $itemStmt=$pdo->prepare('INSERT INTO sale_items(sale_id,product_id,barcode,product_name,unit,qty,price,cost_price,line_total) VALUES(?,?,?,?,?,?,?,?,?)');
   $stockStmt=$pdo->prepare('UPDATE products SET stock=stock-? WHERE id=?');
   $movement=$pdo->prepare("INSERT INTO stock_movements(product_id,user_id,movement_type,qty,note) VALUES(?,?,'keluar',?,'Penjualan')");
   foreach($prepared as $r){$p=$r['product'];$itemStmt->execute([$saleId,$p['id'],$p['barcode'],$p['name'],$p['unit'],$r['qty'],$p['sell_price'],$p['buy_price'],$r['line']]);$stockStmt->execute([$r['qty'],$p['id']]);$movement->execute([$p['id'],current_user()['id'],$r['qty']]);}
   $pdo->commit();redirect('receipt.php?id='.$saleId);
 } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e instanceof RuntimeException?$e->getMessage():'Transaksi gagal. Periksa data dan coba lagi.';}
}
$pageTitle='Kasir / Penjualan';require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h1>Kasir / Penjualan</h1><div class="muted">Cari produk dengan nama atau scan barcode menggunakan scanner USB.</div></div></div>
<?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<div class="cards2"><section class="card"><h2>Pilih Produk</h2><div class="field"><label>Scan atau cari barcode/nama produk</label><input id="productSearch" class="control" placeholder="Scan barcode lalu Enter..." autocomplete="off"></div><div id="productResults" class="product-search-results"></div><p class="small muted">Scanner barcode USB umumnya bekerja seperti keyboard dan mengirim Enter setelah scan.</p></section>
<section class="card"><h2>Keranjang Transaksi</h2><form method="post" id="saleForm"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="items_json" id="itemsJson" value="[]">
<div class="table-wrap"><table class="cart-table"><thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Total</th><th></th></tr></thead><tbody id="cartBody"><tr><td colspan="5" class="empty">Belum ada barang.</td></tr></tbody></table></div>
<div class="summary-box" style="margin-top:15px"><div class="summary-line"><span>Subtotal</span><strong id="subtotal">Rp0</strong></div>
<div class="field" style="margin:10px 0"><label>Diskon (Rp)</label><input id="discount" name="discount" type="number" min="0" step="1" value="0"></div>
<div class="summary-line"><span>Total</span><strong id="total">Rp0</strong></div>
<div class="field" style="margin:10px 0"><label>Uang diterima (Rp)</label><input id="paid" name="paid" type="number" min="0" step="1" required></div>
<div class="summary-line"><span>Perkiraan kembalian</span><strong id="change">Rp0</strong></div>
<div class="actions"><button class="btn" type="submit">Simpan Transaksi & Cetak Struk</button><button class="btn btn-secondary" type="button" onclick="location.reload()">Reset</button></div>
</div></form></section></div>
<script>window.APP_BASE = <?=json_encode(BASE_URL, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;</script>
<?php require __DIR__.'/../includes/footer.php';?>
