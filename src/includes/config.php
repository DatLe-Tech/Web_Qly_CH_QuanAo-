<?php
session_start();
$pdo = new PDO(
  'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . (getenv('DB_NAME') ?: 'qly_ch_quan_ao') . ';charset=utf8mb4',
  getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: 'root',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return number_format((float)$n, 0, ',', '.') . ' ₫'; }
function need_login($admin = false) {
  if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
  if ($admin && $_SESSION['user']['role'] !== 'admin') { http_response_code(403); exit('Trang này chỉ dành cho quản lý.'); }
}
function page_head($title, $active) {
  $u = $_SESSION['user'];
  $menu = ['index.php' => 'Tổng quan', 'products.php' => 'Sản phẩm', 'sales.php' => 'Bán hàng', 'invoices.php' => 'Hóa đơn'];
  if ($u['role'] === 'admin') $menu['stats.php'] = 'Thống kê';
  echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
  echo '<title>' . h($title) . ' · Cửa hàng quần áo</title><link rel="stylesheet" href="style.css"></head><body><aside><div class="brand">Cửa hàng<br>quần áo</div><nav>';
  foreach ($menu as $f => $n) echo '<a href="' . $f . '"' . ($f === $active ? ' class="on"' : '') . '>' . $n . '</a>';
  echo '</nav><div class="me">' . h($u['full_name']) . '<br><a href="logout.php">Đăng xuất</a></div></aside><main><h1>' . h($title) . '</h1>';
}
function page_foot() { echo '</main></body></html>'; }
