<?php
require 'includes/config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $st = $pdo->prepare('SELECT * FROM Users WHERE username = ?');
  $st->execute([trim($_POST['username'] ?? '')]);
  $u = $st->fetch();
  if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
    session_regenerate_id(true);
    unset($u['password']);
    $_SESSION['user'] = $u;
    header('Location: index.php'); exit;
  }
  $err = 'Sai tên đăng nhập hoặc mật khẩu.';
}
?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng nhập</title><link rel="stylesheet" href="style.css"></head>
<body class="login"><form method="post" class="box">
<h1>Bắt đầu ca làm</h1><p class="muted">Đăng nhập để bán hàng.</p>
<?php if ($err) echo '<div class="err">' . h($err) . '</div>'; ?>
<label>Tên đăng nhập<input name="username" required autofocus></label>
<label>Mật khẩu<input name="password" type="password" required></label>
<button class="btn">Đăng nhập</button></form></body></html>
