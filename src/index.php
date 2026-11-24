<?php
require 'includes/config.php'; need_login();
$today = $pdo->query("SELECT COUNT(*) c, COALESCE(SUM(total_amount),0) s FROM Orders WHERE status='completed' AND DATE(order_date)=CURDATE()")->fetch();
$np = $pdo->query('SELECT COUNT(*) FROM Products')->fetchColumn();
$low = $pdo->query('SELECT product_name, quantity FROM Products WHERE quantity <= 10 ORDER BY quantity LIMIT 8')->fetchAll();
page_head('Tổng quan', 'index.php'); ?>
<p>Xin chào <b><?= h($_SESSION['user']['full_name']) ?></b>. Chọn <a href="sales.php">Bán hàng</a> để tạo đơn mới.</p>
<div class="cards">
  <div class="card"><span>Doanh thu hôm nay</span><b><?= money($today['s']) ?></b></div>
  <div class="card"><span>Đơn đã bán hôm nay</span><b><?= $today['c'] ?></b></div>
  <div class="card"><span>Số mặt hàng</span><b><?= $np ?></b></div>
</div>
<h2>Sắp hết hàng (còn 10 hoặc ít hơn)</h2>
<table><tr><th>Sản phẩm</th><th class="r">Tồn kho</th></tr>
<?php foreach ($low as $p) echo '<tr><td>' . h($p['product_name']) . '</td><td class="r">' . $p['quantity'] . '</td></tr>';
if (!$low) echo '<tr><td colspan="2" class="muted">Kho còn đủ hàng.</td></tr>'; ?></table>
<?php page_foot();
