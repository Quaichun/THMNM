<?php include 'app/views/shares/header.php'; ?>

<?php
$grandTotal = 0;
foreach ($orderDetails as $d) {
    $grandTotal += $d->price * $d->quantity;
}
?>

<div class="st-page">
<div class="st-container">

  <div class="st-page-head fade-up">
    <div>
      <h1>Đặt hàng thành công</h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> ›
        <a href="/Product/myOrders">Đơn hàng</a> ›
        <span>Hoàn tất</span>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1.1fr .9fr;gap:24px;align-items:start">

    <div class="st-form-card fade-up">
      <div class="st-form-header" style="padding-bottom:16px">
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:44px;height:44px;border-radius:999px;background:#dcfce7;color:#166534;display:flex;align-items:center;justify-content:center;font-size:1.25rem">✓</div>
          <div>
            <h1 style="margin:0">Đơn hàng đã được tiếp nhận</h1>
            <p style="margin:4px 0 0">Cảm ơn bạn đã mua sắm tại ShopTech</p>
          </div>
        </div>
      </div>

      <div class="st-form-body">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:#f8fafc;margin-bottom:14px">
          <span style="font-weight:700">Mã đơn hàng</span>
          <strong style="font-size:1.05rem;color:var(--primary)">#<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></strong>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
          <div style="border:1px solid var(--border);border-radius:10px;padding:12px">
            <div style="font-size:.8rem;color:var(--text-muted)">Người nhận</div>
            <div style="font-weight:700"><?php echo htmlspecialchars($order->name, ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
          <div style="border:1px solid var(--border);border-radius:10px;padding:12px">
            <div style="font-size:.8rem;color:var(--text-muted)">Điện thoại</div>
            <div style="font-weight:700"><?php echo htmlspecialchars($order->phone, ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
          <div style="border:1px solid var(--border);border-radius:10px;padding:12px">
            <div style="font-size:.8rem;color:var(--text-muted)">Email</div>
            <div style="font-weight:700"><?php echo htmlspecialchars($order->email ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
          </div>
          <div style="border:1px solid var(--border);border-radius:10px;padding:12px">
            <div style="font-size:.8rem;color:var(--text-muted)">Thanh toán</div>
            <div style="font-weight:700"><?php echo (($order->payment_method ?? 'cod') === 'bank') ? 'CHUYỂN KHOẢN/QR' : strtoupper(htmlspecialchars($order->payment_method ?? 'cod', ENT_QUOTES, 'UTF-8')); ?></div>
          </div>
        </div>

        <div style="border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:16px">
          <div style="font-size:.8rem;color:var(--text-muted)">Địa chỉ giao hàng</div>
          <div style="font-weight:700"><?php echo htmlspecialchars($order->address, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
          <div style="width:10px;height:10px;border-radius:999px;background:#16a34a"></div>
          <strong>Đơn hàng đã đặt</strong>
          <span style="font-size:.83rem;color:var(--text-muted)"><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></span>
        </div>
        <div style="height:28px;border-left:2px dashed #cbd5e1;margin:0 0 0 4px"></div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
          <div style="width:10px;height:10px;border-radius:999px;background:#2563eb"></div>
          <strong>Đang xử lý</strong>
        </div>
        <div style="height:28px;border-left:2px dashed #cbd5e1;margin:0 0 0 4px"></div>
        <div style="display:flex;align-items:center;gap:10px;opacity:.8">
          <div style="width:10px;height:10px;border-radius:999px;background:#94a3b8"></div>
          <strong>Đang giao hàng</strong>
        </div>
      </div>
    </div>

    <div class="st-cart-summary-box fade-up" style="position:static">
      <div class="st-summary-title"><i class="bi bi-receipt"></i> Tóm tắt đơn hàng</div>

      <div style="display:flex;flex-direction:column;gap:12px;max-height:420px;overflow:auto;padding-right:4px">
        <?php foreach ($orderDetails as $d): $sub = $d->price * $d->quantity; ?>
          <div style="display:grid;grid-template-columns:52px 1fr auto;gap:10px;align-items:center">
            <div style="width:52px;height:52px;border-radius:8px;overflow:hidden;background:var(--bg-page);display:flex;align-items:center;justify-content:center">
              <?php if (!empty($d->product_image)): ?>
                <img src="/<?php echo htmlspecialchars($d->product_image, ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:100%;height:100%;object-fit:cover">
              <?php else: ?>
                <span>📦</span>
              <?php endif; ?>
            </div>
            <div style="min-width:0">
              <div style="font-weight:700;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                <?php echo htmlspecialchars($d->product_name, ENT_QUOTES, 'UTF-8'); ?>
              </div>
              <div style="font-size:.8rem;color:var(--text-muted)">
                SL <?php echo (int)$d->quantity; ?> x <?php echo number_format($d->price, 0, ',', '.'); ?>₫
              </div>
            </div>
            <div style="font-weight:800;color:var(--primary);white-space:nowrap">
              <?php echo number_format($sub, 0, ',', '.'); ?>₫
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="st-summary-divider"></div>
      <div class="st-summary-row">
        <span>Tạm tính</span>
        <span><?php echo number_format($grandTotal, 0, ',', '.'); ?>₫</span>
      </div>
      <div class="st-summary-row">
        <span>Phí vận chuyển</span>
        <span style="color:#0f766e">Miễn phí</span>
      </div>
      <div class="st-summary-divider"></div>
      <div class="st-summary-total-row">
        <span>Tổng thanh toán</span>
        <span class="st-summary-total-price"><?php echo number_format($grandTotal, 0, ',', '.'); ?>₫</span>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="/Product" class="btn btn-primary">Tiếp tục mua sắm</a>
        <a href="/Product/myOrders" class="btn btn-outline">Xem đơn hàng của tôi</a>
      </div>
    </div>

  </div>
</div>
</div>

<style>
@media (max-width: 980px) {
  .st-container > div[style*="grid-template-columns:1.1fr .9fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php include 'app/views/shares/footer.php'; ?>
