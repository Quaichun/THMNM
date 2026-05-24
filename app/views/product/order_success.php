<?php include 'app/views/shares/header.php'; ?>

<div class="st-page">
<div class="st-container">

  <!-- Step bar -->
  <div class="st-steps fade-up">
    <div class="st-step done">
      <div class="st-step-circle">✓</div><span>Giỏ hàng</span>
    </div>
    <div class="st-step-line done"></div>
    <div class="st-step done">
      <div class="st-step-circle">✓</div><span>Thanh toán</span>
    </div>
    <div class="st-step-line done"></div>
    <div class="st-step active">
      <div class="st-step-circle">✓</div><span>Hoàn tất</span>
    </div>
  </div>

  <!-- Success card -->
  <div class="st-success-card fade-up">
    <div class="st-success-icon">🎉</div>
    <h1>Đặt hàng thành công!</h1>
    <p>Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đang được xử lý.</p>

    <div class="st-order-code">
      Mã đơn hàng: <strong>#<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></strong>
    </div>

    <!-- Order info -->
    <div class="st-success-info">
      <div class="st-success-info-row">
        <span>👤 Người nhận</span>
        <strong><?php echo htmlspecialchars($order->name, ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <div class="st-success-info-row">
        <span>📞 Điện thoại</span>
        <strong><?php echo htmlspecialchars($order->phone, ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <div class="st-success-info-row">
        <span>📍 Địa chỉ</span>
        <strong><?php echo htmlspecialchars($order->address, ENT_QUOTES, 'UTF-8'); ?></strong>
      </div>
      <div class="st-success-info-row">
        <span>🕐 Ngày đặt</span>
        <strong><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></strong>
      </div>
    </div>

    <!-- Status timeline -->
    <div class="st-status-timeline">
      <div class="st-timeline-step done">
        <div class="st-tl-dot"><i class="bi bi-check-circle-fill"></i></div>
        <div class="st-tl-info">
          <strong>Đơn hàng đã đặt</strong>
          <small><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></small>
        </div>
      </div>
      <div class="st-timeline-line done"></div>
      <div class="st-timeline-step active">
        <div class="st-tl-dot"><i class="bi bi-gear-fill"></i></div>
        <div class="st-tl-info">
          <strong>Đang xử lý</strong>
          <small>Shop đang chuẩn bị hàng</small>
        </div>
      </div>
      <div class="st-timeline-line"></div>
      <div class="st-timeline-step">
        <div class="st-tl-dot"><i class="bi bi-truck"></i></div>
        <div class="st-tl-info">
          <strong>Đang giao hàng</strong>
          <small>Dự kiến 2–4 ngày</small>
        </div>
      </div>
      <div class="st-timeline-line"></div>
      <div class="st-timeline-step">
        <div class="st-tl-dot"><i class="bi bi-house-check"></i></div>
        <div class="st-tl-info">
          <strong>Đã giao hàng</strong>
          <small>Hoàn thành</small>
        </div>
      </div>
    </div>

    <!-- Products in order -->
    <div class="st-success-items">
      <h3>Sản phẩm đã đặt</h3>
      <?php
      $grandTotal = 0;
      foreach ($orderDetails as $d):
          $sub = $d->price * $d->quantity;
          $grandTotal += $sub;
      ?>
      <div class="st-success-item">
        <div class="st-co-item-img" style="width:56px;height:56px;flex-shrink:0">
          <?php if (!empty($d->image)): ?>
            <img src="/<?php echo htmlspecialchars($d->image, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="" style="width:100%;height:100%;object-fit:cover;border-radius:8px">
          <?php else: ?>
            <div class="st-co-item-img-ph" style="width:56px;height:56px;font-size:1.5rem">📦</div>
          <?php endif; ?>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:700;font-size:.92rem">
            <?php echo htmlspecialchars($d->product_name, ENT_QUOTES, 'UTF-8'); ?>
          </div>
          <div style="font-size:.82rem;color:var(--text-muted)">
            Số lượng: <?php echo $d->quantity; ?> ×
            <?php echo number_format($d->price, 0, ',', '.'); ?>₫
          </div>
        </div>
        <div style="font-weight:800;color:var(--primary);white-space:nowrap">
          <?php echo number_format($sub, 0, ',', '.'); ?>₫
        </div>
      </div>
      <?php endforeach; ?>

      <div class="st-success-total">
        <span>Tổng thanh toán</span>
        <span class="st-summary-total-price">
          <?php echo number_format($grandTotal, 0, ',', '.'); ?>₫
        </span>
      </div>
    </div>

    <!-- Actions -->
    <div class="st-success-actions">
      <a href="/Product" class="btn btn-primary btn-lg">
        🏠 Tiếp tục mua sắm
      </a>
      <a href="/Product/myOrders" class="btn btn-outline btn-lg">
        📋 Xem đơn hàng của tôi
      </a>
    </div>
  </div>

</div>
</div>

<?php include 'app/views/shares/footer.php'; ?>