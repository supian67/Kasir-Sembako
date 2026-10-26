<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$message=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $action=(string)($_POST['action']??'save');
 if($action==='stock'){
   $id=(int)($_POST['id']??0); $qty=(float)($_POST['qty']??0); $note=trim((string)($_POST['note']??'Stok masuk'));
   if($id<1||$qty<=0) $error='Produk dan jumlah stok harus valid.';
   else { try{$pdo->beginTransaction();$stmt=$pdo->prepare('SELECT id FROM products WHERE id=? FOR UPDATE');$stmt->execute([$id]);if(!$stmt->fetch())throw new RuntimeException('Produk tidak ditemukan.');$pdo->prepare('UPDATE products SET stock=stock+? WHERE id=?')->execute([$qty,$id]);$pdo->prepare("INSERT INTO stock_movements(product_id,user_id,movement_type,qty,note) VALUES(?,?,'masuk',?,?)")->execute([$id,current_user()['id'],$qty,$note]);$pdo->commit();$message='Stok berhasil ditambahkan.';}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error='Gagal memperbarui stok.';} }
 } else {
   $id=(int)($_POST['id']??0);$barcode=trim((string)($_POST['barcode']??''));$name=trim((string)($_POST['name']??''));$category=trim((string)($_POST['category']??'Umum'));$unit=trim((string)($_POST['unit']??'pcs'));$buy=(float)($_POST['buy_price']??0);$sell=(float)($_POST['sell_price']??0);$stock=(float)($_POST['stock']??0);$min=(float)($_POST['min_stock']??0);
   if($barcode===''||$name===''||$buy<0||$sell<0||$stock<0||$min<0)$error='Lengkapi data produk dengan nilai yang benar.';
   else try {if($id){$stmt=$pdo->prepare('UPDATE products SET barcode=?,name=?,category=?,unit=?,buy_price=?,sell_price=?,stock=?,min_stock=? WHERE id=?');$stmt->execute([$barcode,$name,$category,$unit,$buy,$sell,$stock,$min,$id]);$message='Produk berhasil diperbarui.';}else{$stmt=$pdo->prepare('INSERT INTO products(barcode,name,category,unit,buy_price,sell_price,stock,min_stock) VALUES(?,?,?,?,?,?,?,?)');$stmt->execute([$barcode,$name,$category,$unit,$buy,$sell,$stock,$min]);$message='Produk berhasil ditambahkan.';}}
   catch(PDOException $e){$error='Barcode sudah digunakan atau data tidak valid.';}
 }
}
$edit=null;if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM products WHERE id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch()?:null;}
$q=trim((string)($_GET['q']??''));if($q!==''){$st=$pdo->prepare('SELECT * FROM products WHERE name LIKE ? OR barcode LIKE ? ORDER BY name');$like='%'.$q.'%';$st->execute([$like,$like]);$products=$st->fetchAll();}else{$products=$pdo->query('SELECT * FROM products ORDER BY name')->fetchAll();}
$pageTitle='Produk & Stok';require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h1>Produk & Stok</h1><div class="muted">Admin dapat mengelola produk dan menambah stok.</div></div></div>
<?php if($message):?><div class="alert"><?=e($message)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<div class="cards2"><section class="card"><h2><?=$edit?'Edit Produk':'Tambah Produk'?></h2>
<form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e($edit['id']??0)?>">
<div class="form-grid">
<div class="field"><label>Barcode</label><input name="barcode" required value="<?=e($edit['barcode']??'')?>"></div>
<div class="field"><label>Nama produk</label><input name="name" required value="<?=e($edit['name']??'')?>"></div>
<div class="field"><label>Kategori</label><input name="category" value="<?=e($edit['category']??'Umum')?>"></div>
<div class="field"><label>Satuan</label><input name="unit" required value="<?=e($edit['unit']??'pcs')?>"></div>
<div class="field"><label>Harga beli (Rp)</label><input type="number" name="buy_price" min="0" step="0.01" required value="<?=e($edit['buy_price']??0)?>"></div>
<div class="field"><label>Harga jual (Rp)</label><input type="number" name="sell_price" min="0" step="0.01" required value="<?=e($edit['sell_price']??0)?>"></div>
<div class="field"><label>Stok awal</label><input type="number" name="stock" min="0" step="0.01" required value="<?=e($edit['stock']??0)?>"></div>
<div class="field"><label>Minimum stok</label><input type="number" name="min_stock" min="0" step="0.01" required value="<?=e($edit['min_stock']??0)?>"></div>
</div><div class="actions"><button class="btn">Simpan Produk</button><?php if($edit):?><a class="btn btn-secondary" href="<?=BASE_URL?>/products.php">Batal</a><?php endif;?></div></form>
<hr style="border:0;border-top:1px solid #e5ebef;margin:22px 0"><h3>Tambah Stok</h3>
<form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="stock">
<div class="field" style="margin-bottom:10px"><label>Produk</label><select name="id" required><option value="">Pilih produk</option><?php foreach($products as $p):?><option value="<?=$p['id']?>"><?=e($p['name'])?> — stok <?=e($p['stock'])?></option><?php endforeach;?></select></div>
<div class="form-grid"><div class="field"><label>Jumlah masuk</label><input type="number" name="qty" min="0.01" step="0.01" required></div><div class="field"><label>Catatan</label><input name="note" value="Stok masuk"></div></div><div class="actions"><button class="btn btn-secondary">Tambah Stok</button></div></form></section>
<section class="card"><h2>Daftar Produk</h2><form class="searchbar" method="get"><input class="control" name="q" placeholder="Cari nama/barcode..." value="<?=e($q)?>"><button class="btn">Cari</button></form>
<div class="table-wrap"><table><thead><tr><th>Barcode</th><th>Produk</th><th>Harga jual</th><th>Stok</th><th></th></tr></thead><tbody>
<?php foreach($products as $p):?><tr><td><?=e($p['barcode'])?></td><td><?=e($p['name'])?></td><td><?=money($p['sell_price'])?></td><td class="<?=$p['stock']<=$p['min_stock']?'low-stock':''?>"><?=e($p['stock'])?> <?=e($p['unit'])?></td><td><a class="btn btn-secondary" href="?edit=<?=$p['id']?>">Edit</a></td></tr><?php endforeach;?>
<?php if(!$products):?><tr><td colspan="5" class="empty">Belum ada produk.</td></tr><?php endif;?>
</tbody></table></div></section></div>
<?php require __DIR__.'/../includes/footer.php';?>
