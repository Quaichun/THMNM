<?php include 'app/views/shares/header.php'; ?>

<div class="st-page">
<div class="st-container">

  <div class="st-page-head fade-up">
    <div>
      <h1>🧾 Chi tiết đơn hàng
        #<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?>
      </h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> ›
        <a href="/Product/myOrders">Đơn hàng</a> ›
        <span>Chi tiết</span>
      </div>
    </div>
    <a href="/Product/myOrders" class="btn btn-secondary">
      ← Quay lại
    </a>
  </div>

  <div class="st-order-detail-layout">

    <!-- Left: items -->
    <div>
      <div class="st-table-card fade-up">
        <div class="st-table-header">
          <h1>Sản phẩm đã đặt</h1>
        </div>
        <table class="st-table">
          <thead>
            <tr>
              <th colspan="2">Sản phẩm</th>
              <th>Đơn giá</th>
              <th>Số lượng</th>
              <th>Thành tiền</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $grandTotal = 0;
            foreach ($orderDetails as $d):
                $sub = $d->price * $d->quantity;
                $grandTotal += $sub;
            ?>
            <tr>
              <td style="width:60px">
                <?php if (!empty($d->image)): ?>
                  <img src="/<?php echo htmlspecialchars($d->image, ENT_QUOTES, 'UTF-8'); ?>"
                       style="width:54px;height:54px;object-fit:cover;border-radius:8px">
                <?php else: ?>
                  <div style="width:54px;height:54px;background:var(--bg-page);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.5rem">📦</div>
                <?php endif; ?>
              </td>
              <td style="font-weight:700">
                <?php echo htmlspecialchars($d->product_name, ENT_QUOTES, 'UTF-8'); ?>
              </td>
              <td><?php echo number_format($d->price, 0, ',', '.'); ?>₫</td>
              <td><?php echo $d->quantity; ?></td>
              <td style="font-weight:800;color:var(--primary)">
                <?php echo number_format($sub, 0, ',', '.'); ?>₫
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" style="text-align:right;font-weight:700;padding:16px 20px">
                Tổng thanh toán
              </td>
              <td style="font-weight:800;font-size:1.1rem;color:var(--primary);padding:16px 20px">
                <?php echo number_format($grandTotal, 0, ',', '.'); ?>₫
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Right: info + status -->
    <div class="st-order-detail-sidebar fade-up">

      <!-- Customer info -->
      <div class="st-cart-summary-box" style="margin-bottom:20px">
        <div class="st-summary-title"><i class="bi bi-person-fill"></i> Thông tin giao hàng</div>
        <div class="st-summary-rows">
          <div class="st-summary-row">
            <span>Người nhận</span>
            <strong><?php echo htmlspecialchars($order->name, ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div class="st-summary-row">
            <span>Điện thoại</span>
            <strong><?php echo htmlspecialchars($order->phone, ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <div class="st-summary-row">
            <span>Ngày đặt</span>
            <strong><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></strong>
          </div>
        </div>
        <div class="st-summary-divider"></div>
        <div style="font-size:.88rem;color:var(--text-muted)">📍 Địa chỉ</div>
        <div style="font-weight:700;margin-top:4px">
          <?php echo htmlspecialchars($order->address, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      </div>

      <!-- Status timeline -->
      <div class="st-cart-summary-box">
        <div class="st-summary-title"><i class="bi bi-clock-history"></i> Trạng thái đơn hàng</div>
        <div class="st-status-timeline" style="flex-direction:column;gap:0">
          <div class="st-timeline-step done">
            <div class="st-tl-dot"><i class="bi bi-check-circle-fill"></i></div>
            <div class="st-tl-info">
              <strong>Đã đặt hàng</strong>
              <small><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></small>
            </div>
          </div>
          <div class="st-timeline-line-v done"></div>
          <div class="st-timeline-step active">
            <div class="st-tl-dot"><i class="bi bi-gear-fill"></i></div>
            <div class="st-tl-info">
              <strong>Đang xử lý</strong>
              <small>Shop đang chuẩn bị</small>
            </div>
          </div>
          <div class="st-timeline-line-v"></div>
          <div class="st-timeline-step">
            <div class="st-tl-dot"><i class="bi bi-truck"></i></div>
            <div class="st-tl-info">
              <strong>Đang giao hàng</strong>
              <small>Dự kiến 2–4 ngày</small>
            </div>
          </div>
          <div class="st-timeline-line-v"></div>
          <div class="st-timeline-step">
            <div class="st-tl-dot"><i class="bi bi-house-check"></i></div>
            <div class="st-tl-info">
              <strong>Đã giao hàng</strong>
              <small>Hoàn thành</small>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>
</div>

<?php include 'app/views/shares/footer.php'; ?>