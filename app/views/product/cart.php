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
      <div class="st-cart-list">

        <?php foreach ($cart as $id => $item):
          $subtotal = $item['price'] * $item['quantity'];
        ?>
        <div class="st-cart-row fade-up" id="cart-row-<?php echo $id; ?>">

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
              <a href="/Product/decreaseQuantity/<?php echo $id; ?>"
                 class="st-qty-btn st-qty-minus" title="Giảm">−</a>
              <span class="st-qty-val"><?php echo $item['quantity']; ?></span>
              <a href="/Product/increaseQuantity/<?php echo $id; ?>"
                 class="st-qty-btn st-qty-plus" title="Tăng">+</a>
            </div>
          </div>

          <!-- Subtotal + delete -->
          <div class="st-cart-right">
            <div class="st-cart-subtotal">
              <?php echo number_format($subtotal, 0, ',', '.'); ?>₫
            </div>
            <a href="/Product/removeFromCart/<?php echo $id; ?>"
               class="st-cart-remove btn-delete-confirm"
               title="Xóa sản phẩm này">
              <i class="bi bi-x-circle-fill"></i>
            </a>
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
            <span><?php echo $totalQty; ?> sản phẩm</span>
          </div>
          <div class="st-summary-row">
            <span>Tạm tính</span>
            <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
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
          <span class="st-summary-total-price">
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

<?php include 'app/views/shares/footer.php'; ?>