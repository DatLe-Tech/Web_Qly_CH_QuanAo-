<?php
require 'includes/config.php'; need_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json; charset=utf-8');
  $items = json_decode(file_get_contents('php://input'), true)['items'] ?? [];
  try {
    if (!$items) throw new Exception('Giỏ hàng đang trống.');
    $pdo->beginTransaction();
    $total = 0; $lines = [];
    foreach ($items as $it) {
      $qty = (int)$it['qty'];
      $st = $pdo->prepare('SELECT * FROM Products WHERE id=? FOR UPDATE'); $st->execute([(int)$it['id']]); $p = $st->fetch();
      if (!$p || $qty < 1) throw new Exception('Sản phẩm không hợp lệ.');
      if ($p['quantity'] < $qty) throw new Exception('Không đủ hàng: ' . $p['product_name'] . ' (còn ' . $p['quantity'] . ').');
      $total += $p['price'] * $qty; $lines[] = [$p['id'], $qty, $p['price']];
    }
    $pdo->prepare("INSERT INTO Orders (user_id,total_amount,status) VALUES (?,?, 'completed')")->execute([$_SESSION['user']['id'], $total]);
    $oid = $pdo->lastInsertId();
    foreach ($lines as [$pid, $qty, $price]) {
      $pdo->prepare('INSERT INTO OrderDetails (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)')->execute([$oid, $pid, $qty, $price]);
      $pdo->prepare('UPDATE Products SET quantity = quantity - ? WHERE id=?')->execute([$qty, $pid]);
    }
    $pdo->commit();
    echo json_encode(['ok' => true, 'order_id' => $oid, 'total' => $total]);
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400); echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
  }
  exit;
}
$products = $pdo->query('SELECT id, product_name, category, price, quantity FROM Products ORDER BY product_name')->fetchAll();
page_head('Bán hàng', 'sales.php'); ?>
<div class="pos">
  <section>
    <div class="row"><input id="q" placeholder="Tìm sản phẩm…" autofocus><select id="cat"><option value="">Tất cả danh mục</option></select></div>
    <div id="list" class="plist"></div>
  </section>
  <section class="cart">
    <h2>Đơn hiện tại</h2>
    <div id="lines"></div>
    <div class="sum"><span>Tổng tiền</span><b id="total">0 ₫</b></div>
    <label>Khách đưa (₫)<input id="paid" type="number" min="0" step="1000"></label>
    <div class="sum"><span>Tiền thối</span><b id="change">0 ₫</b></div>
    <div id="msg"></div>
    <button class="btn" id="pay">Thanh toán</button> <button class="link" id="clear">Xóa đơn</button>
  </section>
</div>
<script>
const P = <?= json_encode($products, JSON_UNESCAPED_UNICODE) ?>, cart = {};
const $ = id => document.getElementById(id), fmt = n => Number(n).toLocaleString('vi-VN') + ' ₫';
[...new Set(P.map(p => p.category))].forEach(c => $('cat').add(new Option(c, c)));
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function renderList() {
  const q = $('q').value.toLowerCase(), c = $('cat').value;
  $('list').innerHTML = P.filter(p => (!c || p.category === c) && p.product_name.toLowerCase().includes(q)).map(p =>
    `<button class="prod" ${p.quantity < 1 ? 'disabled' : ''} onclick="add(${p.id})"><b>${esc(p.product_name)}</b><span>${fmt(p.price)}</span><small>Còn ${p.quantity}</small></button>`).join('') || '<p class="muted">Không có sản phẩm phù hợp.</p>';
}
function add(id) { const p = P.find(x => x.id == id); if ((cart[id] || 0) < p.quantity) cart[id] = (cart[id] || 0) + 1; renderCart(); }
function setQty(id, d) { const p = P.find(x => x.id == id); cart[id] = Math.min(p.quantity, (cart[id] || 0) + d); if (cart[id] < 1) delete cart[id]; renderCart(); }
function total() { return Object.entries(cart).reduce((s, [id, q]) => s + q * P.find(p => p.id == id).price, 0); }
function renderCart() {
  $('lines').innerHTML = Object.entries(cart).map(([id, q]) => { const p = P.find(x => x.id == id);
    return `<div class="line"><div>${esc(p.product_name)}<small>${fmt(p.price)}</small></div><div class="qty"><button onclick="setQty(${id},-1)">−</button>${q}<button onclick="setQty(${id},1)">+</button></div><b>${fmt(q * p.price)}</b></div>`; }).join('') || '<p class="muted">Bấm vào sản phẩm để thêm vào đơn.</p>';
  const t = total(); $('total').textContent = fmt(t);
  $('change').textContent = fmt(Math.max(0, (+$('paid').value || 0) - t));
}
$('q').oninput = $('cat').onchange = renderList; $('paid').oninput = renderCart;
$('clear').onclick = () => { for (const k in cart) delete cart[k]; $('paid').value = ''; renderCart(); };
$('pay').onclick = async () => {
  const items = Object.entries(cart).map(([id, qty]) => ({ id: +id, qty }));
  const r = await fetch('sales.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ items }) });
  const d = await r.json();
  if (d.ok) { items.forEach(i => P.find(p => p.id == i.id).quantity -= i.qty); $('clear').click(); renderList();
    $('msg').innerHTML = `<div class="ok">Đã lưu hóa đơn #${d.order_id} – ${fmt(d.total)}. <a href="invoices.php?id=${d.order_id}">Xem hóa đơn</a></div>`;
  } else $('msg').innerHTML = `<div class="err">${esc(d.error)}</div>`;
};
renderList(); renderCart();
</script>
<?php page_foot();
