<?php
require 'includes/config.php'; need_login(true);
$days = $pdo->query("SELECT DATE(order_date) d, COUNT(*) c, SUM(total_amount) s FROM Orders WHERE status='completed' AND order_date >= CURDATE() - INTERVAL 6 DAY GROUP BY d ORDER BY d DESC")->fetchAll();
$months = $pdo->query("SELECT DATE_FORMAT(order_date,'%m/%Y') m, COUNT(*) c, SUM(total_amount) s FROM Orders WHERE status='completed' GROUP BY m ORDER BY MIN(order_date) DESC LIMIT 12")->fetchAll();
$top = $pdo->query("SELECT p.product_name, SUM(d.quantity) q, SUM(d.quantity*d.unit_price) s FROM OrderDetails d JOIN Products p ON p.id=d.product_id JOIN Orders o ON o.id=d.order_id WHERE o.status='completed' GROUP BY p.id ORDER BY q DESC LIMIT 5")->fetchAll();
$all = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM Orders WHERE status='completed'")->fetchColumn();
$max = max([1, ...array_column($days, 's')]);
page_head('Thống kê doanh thu', 'stats.php'); ?>
<div class="cards"><div class="card"><span>Tổng doanh thu</span><b><?= money($all) ?></b></div></div>
<h2>7 ngày gần nhất</h2>
<table><tr><th>Ngày</th><th class="r">Số đơn</th><th>Doanh thu</th></tr>
<?php foreach ($days as $r) echo '<tr><td>' . h($r['d']) . '</td><td class="r">' . $r['c'] . '</td><td><div class="bar" style="width:' . round($r['s'] / $max * 100) . '%"></div>' . money($r['s']) . '</td></tr>';
if (!$days) echo '<tr><td colspan="3" class="muted">Chưa có đơn trong 7 ngày qua.</td></tr>'; ?></table>
<h2>Theo tháng</h2>
<table><tr><th>Tháng</th><th class="r">Số đơn</th><th class="r">Doanh thu</th></tr>
<?php foreach ($months as $r) echo '<tr><td>' . h($r['m']) . '</td><td class="r">' . $r['c'] . '</td><td class="r">' . money($r['s']) . '</td></tr>'; ?></table>
<h2>Bán chạy nhất</h2>
<table><tr><th>Sản phẩm</th><th class="r">Đã bán</th><th class="r">Doanh thu</th></tr>
<?php foreach ($top as $r) echo '<tr><td>' . h($r['product_name']) . '</td><td class="r">' . $r['q'] . '</td><td class="r">' . money($r['s']) . '</td></tr>'; ?></table>
<?php page_foot();
