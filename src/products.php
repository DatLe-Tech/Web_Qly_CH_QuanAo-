<?php
require 'includes/config.php'; need_login();
$admin = $_SESSION['user']['role'] === 'admin'; $msg = '';
if ($admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $a = $_POST['action'] ?? '';
  try {
    if ($a === 'delete') {
      $pdo->prepare('DELETE FROM Products WHERE id=?')->execute([$_POST['id']]); $msg = 'Đã xóa sản phẩm.';
    } else {
      $d = [trim($_POST['product_name']), trim($_POST['category']), max(0, (float)$_POST['price']), max(0, (int)$_POST['quantity']), trim($_POST['description'])];
      if ($a === 'add') { $pdo->prepare('INSERT INTO Products (product_name,category,price,quantity,description) VALUES (?,?,?,?,?)')->execute($d); $msg = 'Đã thêm sản phẩm.'; }
      if ($a === 'edit') { $pdo->prepare('UPDATE Products SET product_name=?,category=?,price=?,quantity=?,description=? WHERE id=?')->execute([...$d, $_POST['id']]); $msg = 'Đã lưu thay đổi.'; }
    }
  } catch (PDOException $e) { $msg = 'Không xóa được: sản phẩm này đã có trong hóa đơn.'; }
}
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare('SELECT * FROM Products WHERE product_name LIKE ? OR category LIKE ? ORDER BY id DESC');
$st->execute(["%$q%", "%$q%"]); $rows = $st->fetchAll();
$edit = null;
if ($admin && isset($_GET['edit'])) { $s = $pdo->prepare('SELECT * FROM Products WHERE id=?'); $s->execute([$_GET['edit']]); $edit = $s->fetch(); }
page_head('Sản phẩm', 'products.php');
if ($msg) echo '<div class="ok">' . h($msg) . '</div>'; ?>
<form method="get" class="row"><input name="q" value="<?= h($q) ?>" placeholder="Tìm theo tên hoặc danh mục"><button class="btn">Tìm kiếm</button></form>
<?php if ($admin): $e = $edit ?: ['id' => '', 'product_name' => '', 'category' => '', 'price' => '', 'quantity' => '', 'description' => '']; ?>
<form method="post" class="grid">
  <h2><?= $edit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h2>
  <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>"><input type="hidden" name="id" value="<?= h($e['id']) ?>">
  <label>Tên<input name="product_name" required value="<?= h($e['product_name']) ?>"></label>
  <label>Danh mục<input name="category" value="<?= h($e['category']) ?>"></label>
  <label>Giá (₫)<input name="price" type="number" min="0" step="1000" required value="<?= h($e['price']) ?>"></label>
  <label>Số lượng<input name="quantity" type="number" min="0" required value="<?= h($e['quantity']) ?>"></label>
  <label class="wide">Mô tả<input name="description" value="<?= h($e['description']) ?>"></label>
  <div><button class="btn"><?= $edit ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?></button> <?php if ($edit) echo '<a href="products.php">Hủy</a>'; ?></div>
</form>
<?php endif; ?>
<table><tr><th>Tên</th><th>Danh mục</th><th class="r">Giá</th><th class="r">Tồn</th><?= $admin ? '<th></th>' : '' ?></tr>
<?php foreach ($rows as $p): ?>
<tr><td><?= h($p['product_name']) ?><br><small class="muted"><?= h($p['description']) ?></small></td><td><?= h($p['category']) ?></td>
<td class="r"><?= money($p['price']) ?></td><td class="r"><?= $p['quantity'] ?></td>
<?php if ($admin): ?><td class="r"><a href="?edit=<?= $p['id'] ?>">Sửa</a>
<form method="post" class="inline" onsubmit="return confirm('Xóa sản phẩm này?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="link">Xóa</button></form></td><?php endif; ?></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="5" class="muted">Không tìm thấy sản phẩm.</td></tr>'; ?></table>
<?php page_foot();
