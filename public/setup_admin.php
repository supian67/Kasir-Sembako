<?php
require_once __DIR__ . '/../includes/auth.php';
$count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$message = '';
if ($count > 0) {
    $message = 'Tabel pengguna sudah berisi data. Demi keamanan, file setup ini dinonaktifkan. Hapus public/setup_admin.php dari folder proyek.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['name'] ?? 'Administrator'));
    $username = trim((string)($_POST['username'] ?? 'admin'));
    $password = (string)($_POST['password'] ?? '');
    if ($name === '' || !preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username) || strlen($password) < 10) {
        $message = 'Isi nama, username (3-50 karakter huruf/angka/._-), dan password minimal 10 karakter.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users(name,username,password_hash,role) VALUES(?,?,?,'admin')");
        $stmt->execute([$name,$username,password_hash($password, PASSWORD_DEFAULT)]);
        $message = 'Akun admin berhasil dibuat. Hapus public/setup_admin.php sekarang, lalu login.';
    }
}
$pageTitle='Setup Admin';
require __DIR__ . '/../includes/header.php';
?>
<div class="login-wrap"><div class="card"><h1>Setup Admin Awal</h1>
<div class="alert">Gunakan hanya saat pertama kali memasang aplikasi. Setelah admin dibuat, hapus file ini.</div>
<?php if ($message): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>
<?php if ($count === 0): ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="field" style="margin-bottom:12px"><label>Nama admin</label><input name="name" value="Administrator" required></div>
<div class="field" style="margin-bottom:12px"><label>Username admin</label><input name="username" value="admin" required></div>
<div class="field"><label>Password (minimal 10 karakter)</label><input type="password" name="password" minlength="10" required></div>
<div class="actions"><button class="btn">Buat Admin</button></div></form>
<?php endif; ?>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
