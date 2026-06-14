<?php

$cart  = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
$totalQty = array_sum(array_column($cart, 'quantity'));
?>
<?php include 'app/views/shares/header.php'; ?>

<div class="st-page">
  <div class="st-container">

    <!-- Page heading -->
    <div class="st-page-head fade-up">
      <div>
        <h1>🛒 Giỏ hàng của bạn</h1>
        <div class="st-breadcrumb">
          <a href="/Product">Sản phẩm</a>
          <span>›</span>
          <span>Giỏ hàng</span>
        </div>
      </div>
      <?php if (!empty($cart)): ?>
        <a href="/Product/clearCart"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Xóa toàn bộ giỏ hàng?')">
          <i class="bi bi-trash3"></i> Xóa tất cả
        </a>
      <?php endif; ?>
    </div>

    <?php if (empty($cart)): ?>
    <!-- ═══ EMPTY CART ═══ -->
    <div class="st-cart-empty fade-up">
      <div class="st-cart-empty-icon">🛒</div>
      <h2>Giỏ hàng đang trống</h2>
      <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm nhé!</p>
      <a href="/Product" class="btn btn-primary btn-lg">
        <i class="bi bi-arrow-left"></i> Tiếp tục mua hàng
      </a>
    </div>

    <?php else: ?>
    <!-- ═══ CART LAYOUT ═══ -->
    <div class="st-cart-layout">

      <!-- ─── Left: product list ─── -->
      <div class="st-cart-list" id="cartListContainer">

        <?php foreach ($cart as $id => $item):
          $subtotal = $item['price'] * $item['quantity'];
        ?>
        <div class="st-cart-row fade-up" id="cart-item-<?php echo $id; ?>">

          <!-- Image -->
          <div class="st-cart-img-wrap">
            <?php if (!empty($item['image'])): ?>
              <img src="/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                   alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php else: ?>
              <div class="st-cart-img-placeholder">📦</div>
            <?php endif; ?>
          </div>

          <!-- Info -->
          <div class="st-cart-info">
            <div class="st-cart-name">
              <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="st-cart-unit-price">
              <?php echo number_format($item['price'], 0, ',', '.'); ?>₫ / sản phẩm
            </div>

            <!-- Quantity control -->
            <div class="st-qty-wrap">
              <button type="button" onclick="updateQty(<?php echo $id; ?>, -1)"
                  class="st-qty-btn st-qty-minus" title="Giảm">−</button>
              <span class="st-qty-val" id="qty-<?php echo $id; ?>"><?php echo $item['quantity']; ?></span>
              <button type="button" onclick="updateQty(<?php echo $id; ?>, 1)"
                  class="st-qty-btn st-qty-plus" title="Tăng">+</button>
            </div>
          </div>

          <!-- Subtotal + delete -->
          <div class="st-cart-right">
            <div class="st-cart-subtotal" id="line-total-<?php echo $id; ?>">
              <?php echo number_format($subtotal, 0, ',', '.'); ?>₫
            </div>
            <button type="button" onclick="removeItem(<?php echo $id; ?>)"
               class="st-cart-remove"
               title="Xóa sản phẩm này">
              <i class="bi bi-x-circle-fill"></i>
            </button>
          </div>

        </div>
        <?php endforeach; ?>

      </div>

      <!-- ─── Right: summary box ─── -->
      <div class="st-cart-summary-box fade-up">

        <div class="st-summary-title">
          <i class="bi bi-receipt"></i> Tóm tắt đơn hàng
        </div>

        <div class="st-summary-rows">
          <div class="st-summary-row">
            <span>Số lượng sản phẩm</span>
            <span id="summary-qty"><?php echo $totalQty; ?> sản phẩm</span>
          </div>
          <div class="st-summary-row">
            <span>Tạm tính</span>
            <span id="summary-subtotal"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
          </div>
          <div class="st-summary-row">
            <span>Phí vận chuyển</span>
            <span class="text-success">Miễn phí</span>
          </div>
          <div class="st-summary-row">
            <span>Giảm giá</span>
            <span class="text-danger">−0₫</span>
          </div>
        </div>

        <div class="st-summary-divider"></div>

        <div class="st-summary-total-row">
          <span>Tổng thanh toán</span>
          <span class="st-summary-total-price" id="summary-total">
            <?php echo number_format($total, 0, ',', '.'); ?>₫
          </span>
        </div>

        <div class="st-summary-badges">
          <span class="st-trust-chip">🔒 Thanh toán bảo mật</span>
          <span class="st-trust-chip">🚚 Miễn phí vận chuyển</span>
          <span class="st-trust-chip">↩️ Đổi trả 30 ngày</span>
        </div>

        <a href="/Product/checkout" class="btn btn-primary btn-lg w-100 st-checkout-btn">
          <i class="bi bi-bag-check-fill"></i> Thanh toán ngay
        </a>

        <a href="/Product" class="btn btn-outline w-100 mt-2">
          <i class="bi bi-arrow-left"></i> Tiếp tục mua hàng
        </a>

        <!-- Accepted payments -->
        <div class="st-payment-icons">
          <span title="Tiền mặt">💵</span>
          <span title="Thẻ tín dụng">💳</span>
          <span title="Chuyển khoản">🏦</span>
          <span title="Ví điện tử">📱</span>
        </div>

      </div>

    </div>
    <?php endif; ?>

  </div>
</div>

<script>
async function updateQty(id, delta) {
    const action = delta > 0 ? 'increase' : 'decrease';
    try {
        const res = await apiFetch(`/api/cart/${action}/${id}`, { method: 'POST' });
        const result = await res.json();
        if (result.success) {
            refreshCartUI(result);
        }
    } catch (e) { console.error(e); }
}

async function removeItem(id) {
    if (!confirm('Xóa sản phẩm này?')) return;
    try {
        const res = await apiFetch(`/api/cart/destroy/${id}`, { method: 'DELETE' });
        const result = await res.json();
        if (result.success) {
            const row = document.getElementById(`cart-item-${id}`);
            if (row) row.remove();
            refreshCartUI(result);
        }
    } catch (e) { console.error(e); }
}

async function clearCart() {
    if (!confirm('Xóa toàn bộ giỏ hàng?')) return;
    try {
        const res = await apiFetch('/api/cart/clear', { method: 'DELETE' });
        const result = await res.json();
        if (result.success) {
            location.reload(); // Đơn giản nhất là reload khi dọn sạch
        }
    } catch (e) { console.error(e); }
}

function refreshCartUI(data) {
    // Cập nhật số lượng trên Badge (nếu có)
    const badge = document.getElementById('cartBadge');
    if (badge) {
        badge.textContent = data.cart_count;
        badge.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
        badge.classList.add('bump');
        setTimeout(() => badge.classList.remove('bump'), 300);
    }

    // Cập nhật các dòng item
    if (data.cart) {
        // Collect all currently rendered IDs
        const existingRows = document.querySelectorAll('.st-cart-row');
        const serverIds = data.cart.map(item => item.product_id.toString());
        
        existingRows.forEach(row => {
            const rowId = row.id.replace('cart-item-', '');
            if (!serverIds.includes(rowId)) {
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
        });

        data.cart.forEach(item => {
            const qtyEl = document.getElementById(`qty-${item.product_id}`);
            if (qtyEl) qtyEl.textContent = item.quantity;
            
            const lineTotalEl = document.getElementById(`line-total-${item.product_id}`);
            if (lineTotalEl) lineTotalEl.textContent = parseInt(item.line_total).toLocaleString('vi-VN') + '₫';
        });

        // Nếu giỏ hàng trống sau khi xóa item cuối
        if (data.cart.length === 0) {
            location.reload();
            return;
        }
    }

    // Cập nhật Summary
    const summaryQty = document.getElementById('summary-qty');
    if (summaryQty) summaryQty.textContent = `${data.cart_count} sản phẩm`;

    const summarySubtotal = document.getElementById('summary-subtotal');
    if (summarySubtotal) summarySubtotal.textContent = parseInt(data.subtotal).toLocaleString('vi-VN') + '₫';

    const summaryTotal = document.getElementById('summary-total');
    if (summaryTotal) summaryTotal.textContent = parseInt(data.subtotal).toLocaleString('vi-VN') + '₫';
}
</script>


  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>