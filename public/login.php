<?php
require_once __DIR__ . '/../includes/auth.php';
if (current_user()) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id,name,username,password_hash,role FROM users WHERE username=? AND is_active=1 LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>(int)$user['id'],'name'=>$user['name'],'username'=>$user['username'],'role'=>$user['role']];
        redirect('index.php');
    }
    $error = 'Username atau password salah, atau akun tidak aktif.';
}
$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';
?>
<div class="login-wrap"><div class="card">
<h1>Masuk ke KasirKu</h1><p class="muted">Gunakan akun admin atau kasir toko.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="field" style="margin-bottom:12px"><label>Username</label><input name="username" autocomplete="username" required autofocus></div>
<div class="field"><label>Password</label><input type="password" name="password" autocomplete="current-password" required></div>
<div class="actions"><button class="btn" type="submit">Login</button></div>
</form>
<p class="small muted">Jika belum ada akun, jalankan setup_admin.php sekali setelah impor database, lalu hapus file tersebut.</p>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
