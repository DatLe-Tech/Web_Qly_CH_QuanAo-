<?php
require 'includes/config.php'; need_login();
page_head('Hóa đơn', 'invoices.php');
if (isset($_GET['id'])) {
  $s = $pdo->prepare('SELECT o.*, u.full_name FROM Orders o JOIN Users u ON u.id=o.user_id WHERE o.id=?'); $s->execute([$_GET['id']]); $o = $s->fetch();
  if (!$o) exit('<p>Không tìm thấy hóa đơn.</p>');
  $d = $pdo->prepare('SELECT d.*, p.product_name FROM OrderDetails d JOIN Products p ON p.id=d.product_id WHERE d.order_id=?'); $d->execute([$o['id']]); ?>
  <p><a href="invoices.php">← Danh sách hóa đơn</a></p>
  <div class="receipt"><h2>Hóa đơn #<?= $o['id'] ?></h2>
  <p class="muted"><?= h($o['order_date']) ?> · Nhân viên: <?= h($o['full_name']) ?></p>
  <table><tr><th>Sản phẩm</th><th class="r">SL</th><th class="r">Đơn giá</th><th class="r">Thành tiền</th></tr>
  <?php foreach ($d as $l) echo '<tr><td>' . h($l['product_name']) . '</td><td class="r">' . $l['quantity'] . '</td><td class="r">' . money($l['unit_price']) . '</td><td class="r">' . money($l['quantity'] * $l['unit_price']) . '</td></tr>'; ?>
  <tr><th colspan="3" class="r">Tổng cộng</th><th class="r"><?= money($o['total_amount']) ?></th></tr></table>
  <button class="btn" onclick="print()">In hóa đơn</button></div>
<?php } else {
  $rows = $pdo->query('SELECT o.*, u.full_name FROM Orders o JOIN Users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 100')->fetchAll(); ?>
  <table><tr><th>Mã</th><th>Thời gian</th><th>Nhân viên</th><th class="r">Tổng tiền</th></tr>
  <?php foreach ($rows as $o) echo '<tr><td><a href="?id=' . $o['id'] . '">#' . $o['id'] . '</a></td><td>' . h($o['order_date']) . '</td><td>' . h($o['full_name']) . '</td><td class="r">' . money($o['total_amount']) . '</td></tr>';
  if (!$rows) echo '<tr><td colspan="4" class="muted">Chưa có hóa đơn nào.</td></tr>'; ?></table>
<?php } page_foot();
