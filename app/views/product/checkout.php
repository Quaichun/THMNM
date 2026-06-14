<?php include 'app/views/shares/header.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['quantity'];
$currentPayment = $_POST['payment'] ?? 'cod';
$qrOrderId = isset($nextOrderId) ? (int)$nextOrderId : 0;
$qrContent = 'mh' . $qrOrderId;
$qrUrl = '/Product/paymentQr?amount=' . (int)$total
       . '&code=' . rawurlencode($qrContent);
?>

<div class="st-page">
<div class="st-container">
  <div class="st-page-head fade-up">
    <div>
      <h1>Thanh toán</h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> ›
        <a href="/Product/cart">Giỏ hàng</a> ›
        <span>Thanh toán</span>
      </div>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger fade-up">
      <ul style="margin:0;padding-left:18px">
        <?php foreach ($errors as $e): ?>
          <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1.05fr .95fr;gap:24px;align-items:start">
    <div class="st-form-card fade-up">
      <div class="st-form-header">
        <h1>Giỏ hàng của bạn</h1>
        <p><?php echo count($cart); ?> sản phẩm trong đơn hàng</p>
      </div>
      <div class="st-form-body" style="padding-top:0">
        <?php foreach ($cart as $item): ?>
          <div style="display:grid;grid-template-columns:84px 1fr auto;gap:14px;align-items:center;padding:14px 0;border-bottom:1px solid var(--border)">
            <div style="width:84px;height:84px;border-radius:12px;overflow:hidden;background:var(--bg-page);display:flex;align-items:center;justify-content:center">
              <?php if (!empty($item['image'])): ?>
                <img src="/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>"
                     style="width:100%;height:100%;object-fit:cover">
              <?php else: ?>
                <span style="font-size:1.7rem">📦</span>
              <?php endif; ?>
            </div>
            <div>
              <div style="font-weight:800;margin-bottom:3px">
                <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
              </div>
              <div style="color:var(--text-muted);font-size:.9rem">
                Đơn giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>₫
              </div>
              <div style="margin-top:3px;font-size:.88rem;color:var(--text-muted)">
                Số lượng: <?php echo (int)$item['quantity']; ?>
              </div>
            </div>
            <div style="font-weight:800;color:var(--primary);white-space:nowrap">
              <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
            </div>
          </div>
        <?php endforeach; ?>

        <div style="margin-top:16px;border-radius:12px;background:#f8fbff;border:1px solid #d7e7ff;padding:14px">
          <div style="display:flex;justify-content:space-between;font-size:.92rem;margin-bottom:8px">
            <span>Tạm tính</span>
            <strong><?php echo number_format($total, 0, ',', '.'); ?>₫</strong>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.92rem;margin-bottom:8px">
            <span>Phí vận chuyển</span>
            <strong style="color:#0f766e">Miễn phí</strong>
          </div>
          <div style="height:1px;background:#d7e7ff;margin:10px 0"></div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-weight:700">Tổng cộng</span>
            <span style="font-size:1.25rem;font-weight:800;color:var(--primary)">
              <?php echo number_format($total, 0, ',', '.'); ?>₫
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="st-form-card fade-up">
      <div class="st-form-header">
        <h1>Thông tin đặt hàng</h1>
        <p>Điền thông tin để xác nhận giao hàng</p>
      </div>
      <div class="st-form-body">
        <form method="POST" action="/Product/placeOrder" id="checkoutForm">
          <div class="mb-4">
            <label class="form-label">Họ và tên *</label>
            <input type="text" name="name" class="form-control"
                   value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Nguyễn Văn A" required>
          </div>

          <div class="mb-4">
            <label class="form-label">Số điện thoại *</label>
            <input type="tel" name="phone" class="form-control"
                   value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="0901 234 567" required>
          </div>

          <div class="mb-4">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="you@example.com" required>
          </div>

          <div class="mb-4">
            <label class="form-label">Địa chỉ giao hàng *</label>
            <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label">Ghi chú (tùy chọn)</label>
            <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú cho người giao hàng..."></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label">Phương thức thanh toán</label>
            <div class="st-payment-methods">
              <label class="st-pay-option <?php echo $currentPayment === 'cod' ? 'selected' : ''; ?>">
                <input type="radio" name="payment" value="cod" <?php if ($currentPayment === 'cod') echo 'checked'; ?>>
                <span class="st-pay-icon">💵</span>
                <div>
                  <strong>Tiền mặt (COD)</strong>
                  <small>Thanh toán khi nhận hàng</small>
                </div>
              </label>
              <label class="st-pay-option <?php echo ($currentPayment === 'bank' || $currentPayment === 'qr') ? 'selected' : ''; ?>">
                <input type="radio" name="payment" value="bank" <?php if ($currentPayment === 'bank' || $currentPayment === 'qr') echo 'checked'; ?>>
                <span class="st-pay-icon">🏦</span>
                <div>
                  <strong>Chuyển khoản / QR Code</strong>
                  <small>Thanh toán chuyển khoản hoặc quét mã QR</small>
                </div>
              </label>
            </div>
          </div>

          <div id="checkoutQrBox" class="mb-4" style="display:<?php echo ($currentPayment === 'bank' || $currentPayment === 'qr') ? 'block' : 'none'; ?>;border:1px dashed #bfdbfe;border-radius:12px;padding:14px;background:#f8fbff;">
            <div style="display:flex;justify-content:center;">
              <img
                src="<?php echo htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8'); ?>"
                alt="QR thanh toán"
                style="width:220px;height:220px;max-width:100%;border-radius:10px;border:1px solid #dbeafe;background:#fff;padding:8px;">
            </div>
            <div style="text-align:center;font-weight:700;margin-top:10px;color:#1d4ed8;">
              Quét mã QR để thanh toán ngay
            </div>
            <div style="text-align:center;font-size:.82rem;color:var(--text-muted);margin-top:6px;line-height:1.5;">
              Ngân hàng MB Bank • STK: 0775632430 • Chủ tài khoản: Nguyen Hoai Trung
            </div>
            <div style="text-align:center;font-size:.8rem;color:#64748b;margin-top:4px;">
              Nội dung chuyển khoản: <?php echo htmlspecialchars($qrContent, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          </div>

          <div class="st-form-actions">
            <a href="/Product/cart" class="btn btn-secondary">← Quay lại giỏ hàng</a>
            <button type="submit" class="btn btn-primary btn-lg" id="placeOrderBtn">
              <i class="bi bi-bag-check-fill"></i> Đặt hàng
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>

<style>
@media (max-width: 980px) {
  .st-container > div[style*="grid-template-columns:1.05fr .95fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>

<script>
  (function () {
    const qrBox = document.getElementById('checkoutQrBox');
    const radios = document.querySelectorAll('input[name="payment"]');
    const form = document.getElementById('checkoutForm');
    const btn = document.getElementById('placeOrderBtn');

    if (!qrBox || !radios.length) return;

    function syncQr() {
      const current = document.querySelector('input[name="payment"]:checked');
      qrBox.style.display = current && current.value === 'bank' ? 'block' : 'none';
    }

    radios.forEach(r => r.addEventListener('change', syncQr));
    syncQr();

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                phone: formData.get('phone'),
                email: formData.get('email'),
                address: formData.get('address'),
                payment_method: formData.get('payment'),
                note: formData.get('note')
            };

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';

            try {
                const res = await apiFetch('/api/order/store', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Đã đặt hàng thành công!', '📦');
                    setTimeout(() => {
                        window.location.href = '/Product/myOrders?success=1';
                    }, 1500);
                } else {
                    let msg = result.message || 'Đặt hàng thất bại';
                    if (result.errors) msg = Object.values(result.errors).join('\n');
                    alert(msg);
                }
            } catch (err) {
                alert('Có lỗi xảy ra, vui lòng thử lại');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-bag-check-fill"></i> Đặt hàng';
            }
        });
    }
  })();
</script>


<?php include 'app/views/shares/footer.php'; ?>
