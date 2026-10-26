<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$message=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $name=trim((string)($_POST['name']??'')); $username=trim((string)($_POST['username']??''));
    $password=(string)($_POST['password']??''); $role=(string)($_POST['role']??'kasir');
    if ($name==='' || !preg_match('/^[a-zA-Z0-9_.-]{3,50}$/',$username) || strlen($password)<10 || !in_array($role,['admin','kasir'],true)) $error='Periksa nama, username, role, dan password minimal 10 karakter.';
    else {
        try { $stmt=$pdo->prepare('INSERT INTO users(name,username,password_hash,role) VALUES(?,?,?,?)'); $stmt->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT),$role]); $message='Pengguna berhasil dibuat.'; }
        catch(PDOException $e) { $error='Username sudah digunakan atau data tidak valid.'; }
    }
}
$users=$pdo->query('SELECT id,name,username,role,is_active,created_at FROM users ORDER BY id')->fetchAll();
$pageTitle='Pengguna'; require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h1>Pengguna</h1><div class="muted">Khusus admin. Buat akun kasir dengan password kuat.</div></div></div>
<?php if($message):?><div class="alert"><?=e($message)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<div class="cards2"><section class="card"><h2>Tambah Pengguna</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
<div class="field" style="margin-bottom:12px"><label>Nama lengkap</label><input name="name" required></div>
<div class="field" style="margin-bottom:12px"><label>Username (3-50 karakter: huruf, angka, . _ -)</label><input name="username" required></div>
<div class="field" style="margin-bottom:12px"><label>Password minimal 10 karakter</label><input type="password" name="password" minlength="10" required></div>
<div class="field"><label>Role</label><select name="role"><option value="kasir">Kasir</option><option value="admin">Admin</option></select></div><div class="actions"><button class="btn">Simpan</button></div></form></section>
<section class="card"><h2>Daftar Pengguna</h2><div class="table-wrap"><table><thead><tr><th>Nama</th><th>Username</th><th>Role</th></tr></thead><tbody><?php foreach($users as $u):?><tr><td><?=e($u['name'])?></td><td><?=e($u['username'])?></td><td><?=e($u['role'])?></td></tr><?php endforeach;?></tbody></table></div></section></div>
<?php require __DIR__.'/../includes/footer.php';?>
