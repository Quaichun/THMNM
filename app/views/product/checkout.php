<?php include 'app/views/shares/header.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$cart  = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['quantity'];
?>

<div class="st-page">
<div class="st-container">

  <div class="st-page-head fade-up">
    <div>
      <h1>💳 Thanh toán</h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> ›
        <a href="/Product/cart">Giỏ hàng</a> ›
        <span>Thanh toán</span>
      </div>
    </div>
  </div>

  <!-- Step bar -->
  <!-- <div class="st-steps fade-up">
    <div class="st-step done">
      <div class="st-step-circle">✓</div>
      <span>Giỏ hàng</span>
    </div>
    <div class="st-step-line done"></div>
    <div class="st-step active">
      <div class="st-step-circle">2</div>
      <span>Thanh toán</span>
    </div>
    <div class="st-step-line"></div>
    <div class="st-step">
      <div class="st-step-circle">3</div>
      <span>Hoàn tất</span>
    </div>
  </div> -->

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger fade-up">
      <ul style="margin:0;padding-left:18px">
        <?php foreach ($errors as $e): ?>
          <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="st-checkout-layout">

    <!-- ─── Left: form ─── -->
    <div class="st-checkout-form-wrap fade-up">
      <div class="st-form-card">
        <div class="st-form-header">
          <h1>📋 Thông tin giao hàng</h1>
          <p>Điền đầy đủ thông tin để chúng tôi giao hàng đến bạn</p>
        </div>
        <div class="st-form-body">
          <form method="POST" action="/Product/placeOrder" id="checkoutForm">

            <div class="mb-4">
              <label class="form-label">Họ và tên *</label>
              <input type="text" name="name" class="form-control"
                     placeholder="Nguyễn Văn A"
                     value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                     required>
            </div>

            <div class="mb-4">
              <label class="form-label">Số điện thoại *</label>
              <input type="tel" name="phone" class="form-control"
                     placeholder="0901 234 567"
                     value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                     required>
            </div>

            <div class="mb-4">
              <label class="form-label">Địa chỉ giao hàng *</label>
              <textarea name="address" class="form-control" rows="3"
                        placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố"
                        required><?php echo htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label">Ghi chú (tùy chọn)</label>
              <textarea name="note" class="form-control" rows="2"
                        placeholder="Ghi chú cho người giao hàng..."></textarea>
            </div>

            <!-- Payment method -->
            <div class="mb-4">
              <label class="form-label">Phương thức thanh toán</label>
              <div class="st-payment-methods">
                <label class="st-pay-option selected">
                  <input type="radio" name="payment" value="cod" checked>
                  <span class="st-pay-icon">💵</span>
                  <div>
                    <strong>Tiền mặt (COD)</strong>
                    <small>Thanh toán khi nhận hàng</small>
                  </div>
                </label>
                <label class="st-pay-option">
                  <input type="radio" name="payment" value="bank">
                  <span class="st-pay-icon">🏦</span>
                  <div>
                    <strong>Chuyển khoản</strong>
                    <small>Chuyển khoản ngân hàng</small>
                  </div>
                </label>
              </div>
            </div>

            <div class="st-form-actions">
              <a href="/Product/cart" class="btn btn-secondary">
                ← Quay lại giỏ hàng
              </a>
              <button type="submit" class="btn btn-primary btn-lg" id="placeOrderBtn">
                <i class="bi bi-bag-check-fill"></i> Đặt hàng ngay
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>

    <!-- ─── Right: order summary ─── -->
    <div class="st-checkout-summary fade-up">
      <div class="st-cart-summary-box">
        <div class="st-summary-title">
          <i class="bi bi-bag"></i> Đơn hàng của bạn
        </div>

        <div class="st-co-items">
          <?php foreach ($cart as $item): ?>
          <div class="st-co-item">
            <div class="st-co-item-img">
              <?php if (!empty($item['image'])): ?>
                <img src="/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php else: ?>
                <div class="st-co-item-img-ph">📦</div>
              <?php endif; ?>
              <span class="st-co-qty-badge"><?php echo $item['quantity']; ?></span>
            </div>
            <div class="st-co-item-info">
              <div class="st-co-item-name">
                <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
              </div>
              <div class="st-co-item-price">
                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="st-summary-divider"></div>
        <div class="st-summary-rows">
          <div class="st-summary-row">
            <span>Tạm tính</span>
            <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
          </div>
          <div class="st-summary-row">
            <span>Phí vận chuyển</span>
            <span class="text-success">Miễn phí</span>
          </div>
        </div>
        <div class="st-summary-divider"></div>
        <div class="st-summary-total-row">
          <span>Tổng cộng</span>
          <span class="st-summary-total-price">
            <?php echo number_format($total, 0, ',', '.'); ?>₫
          </span>
        </div>
      </div>
    </div>

  </div>
</div>
</div>

<?php include 'app/views/shares/footer.php'; ?>