<?php include 'app/views/shares/header.php'; ?>

<?php
$statusMap = [
  'pending'    => ['label' => 'Chờ xác nhận', 'class' => 'pending'],
  'processing' => ['label' => 'Đang xử lý', 'class' => 'processing'],
  'shipping'   => ['label' => 'Đang giao hàng', 'class' => 'shipping'],
  'delivered'  => ['label' => 'Đã giao hàng', 'class' => 'delivered'],
  'cancelled'  => ['label' => 'Đã hủy', 'class' => 'cancelled'],
];

$orders = $orders ?? [];
$historyOrders = $historyOrders ?? [];
$deliveredOrders = $deliveredOrders ?? [];
$historyByOrderId = [];
foreach ($historyOrders as $h) {
  $historyByOrderId[(int)$h->order->id] = $h;
}
$deliveryHistoryOrders = [];
foreach ($deliveredOrders as $o) {
  $oid = (int)$o->id;
  if (isset($historyByOrderId[$oid])) {
    $deliveryHistoryOrders[] = $historyByOrderId[$oid];
  }
}
?>

<div class="st-page">
<div class="st-container">

  <div class="st-page-head fade-up">
    <div>
      <h1>📋 Đơn hàng của tôi</h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> › <span>Đơn hàng</span>
      </div>
    </div>
  </div>

  <div class="st-order-tabs fade-up" id="orderTabs">
    <button type="button" class="st-order-tab-btn active" data-tab="ordersPanel">
      <i class="bi bi-receipt"></i>
      Đơn đã đặt
      <span class="st-tab-count"><?php echo count($orders); ?></span>
    </button>
    <button type="button" class="st-order-tab-btn" data-tab="historyPanel">
      <i class="bi bi-clock-history"></i>
      Lịch sử giao hàng
      <span class="st-tab-count"><?php echo count($deliveryHistoryOrders); ?></span>
    </button>
  </div>

  <div id="ordersPanel" class="st-tab-panel active">
    <?php if (empty($orders)): ?>
      <div class="st-cart-empty fade-up">
        <div class="st-cart-empty-icon">📋</div>
        <h2>Chưa có đơn hàng nào</h2>
        <p>Hãy mua sắm để tạo đơn hàng đầu tiên!</p>
        <a href="/Product" class="btn btn-primary btn-lg">🛍️ Mua sắm ngay</a>
      </div>
    <?php else: ?>
      <div class="st-table-card fade-up st-delivered-card">
        <div class="st-table-header">
          <h1>Danh sách đơn đã đặt</h1>
          <span style="font-size:.85rem;opacity:.8"><?php echo count($orders); ?> đơn hàng</span>
        </div>
        <div style="overflow-x:auto">
          <table class="st-table">
            <thead>
              <tr>
                <th>Mã đơn</th>
                <th>Sản phẩm</th>
                <th>Người nhận</th>
                <th>Điện thoại</th>
                <th>Địa chỉ</th>
                <th>Ngày đặt</th>
                <th>Trạng thái</th>
                <th>Chi tiết</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
              <?php $st = $statusMap[$o->status ?? 'pending'] ?? $statusMap['pending']; ?>
              <?php $oh = $historyByOrderId[(int)$o->id] ?? null; ?>
              <tr>
                <td><strong>#<?php echo str_pad($o->id, 6, '0', STR_PAD_LEFT); ?></strong></td>
                <td>
                  <?php if ($oh && $oh->firstItem): ?>
                    <strong><?php echo htmlspecialchars($oh->firstItem->product_name, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <div class="st-delivered-meta"><?php echo (int)$oh->totalQty; ?> sản phẩm</div>
                  <?php else: ?>
                    <span class="st-delivered-empty">-</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($o->name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($o->phone, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="st-delivered-address"><?php echo htmlspecialchars($o->address, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($o->created_at)); ?></td>
                <td><span class="st-order-status <?php echo $st['class']; ?>"><?php echo htmlspecialchars($st['label'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                <td><a href="/Product/orderDetail/<?php echo $o->id; ?>" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Xem</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div id="historyPanel" class="st-tab-panel">
    <?php if (empty($deliveryHistoryOrders)): ?>
      <div class="st-history-empty fade-up">
        <div class="st-history-empty-icon">🕘</div>
        <h3>Chưa có lịch sử giao hàng</h3>
        <p>Các đơn đã giao sẽ hiển thị ở đây.</p>
      </div>
    <?php else: ?>
      <div class="st-oh-list fade-up">
        <?php foreach ($deliveryHistoryOrders as $history): ?>
          <?php
            $o = $history->order;
            $first = $history->firstItem;
            $st = $statusMap[$o->status ?? 'pending'] ?? $statusMap['pending'];
          ?>
          <div class="st-oh-card">
            <div class="st-oh-left">
              <div class="st-oh-code">
                #DH<?php echo str_pad((int)$o->id, 6, '0', STR_PAD_LEFT); ?>
                <span class="st-order-status <?php echo $st['class']; ?>"><?php echo $st['label']; ?></span>
              </div>
              <div class="st-oh-time">Đặt lúc: <?php echo date('d/m/Y H:i', strtotime($o->created_at)); ?></div>

              <div class="st-oh-product">
                <div class="st-oh-product-img">
                  <?php if ($first && !empty($first->product_image)): ?>
                    <img src="/<?php echo htmlspecialchars($first->product_image, ENT_QUOTES, 'UTF-8'); ?>" alt="product">
                  <?php else: ?>
                    <i class="bi bi-image"></i>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="st-oh-product-name"><?php echo htmlspecialchars($first->product_name ?? 'Sản phẩm trong đơn', ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="st-oh-product-meta"><?php echo count($history->details); ?> sản phẩm</div>
                  <div class="st-oh-product-price"><?php echo number_format((float)$history->totalAmount, 0, ',', '.'); ?> đ</div>
                </div>
              </div>

              <div class="st-oh-total">
                <span>Tổng tiền:</span>
                <span class="st-oh-total-amt"><?php echo number_format((float)$history->totalAmount, 0, ',', '.'); ?> đ</span>
              </div>

              <a href="/Product/orderDetail/<?php echo (int)$o->id; ?>" class="st-oh-detail-btn">
                <i class="bi bi-eye"></i> Xem chi tiết đơn hàng
              </a>
            </div>

            <div class="st-oh-right">
              <h3>Lịch sử giao hàng</h3>
              <div class="st-timeline">
                <div class="st-tl-item">
                  <div class="st-tl-dot-col">
                    <div class="st-tl-dot blue"><i class="bi bi-receipt"></i></div>
                    <div class="st-tl-line"></div>
                  </div>
                  <div class="st-tl-content">
                    <div class="st-tl-head">
                      <span class="st-tl-time"><?php echo date('d/m/Y H:i', strtotime($o->created_at)); ?></span>
                      <span class="st-tl-tag blue">Tạo đơn hàng</span>
                    </div>
                    <div class="st-tl-body">Đơn hàng đã được tạo thành công.</div>
                  </div>
                </div>

                <div class="st-tl-item">
                  <div class="st-tl-dot-col">
                    <div class="st-tl-dot amber"><i class="bi bi-pencil-square"></i></div>
                    <div class="st-tl-line"></div>
                  </div>
                  <div class="st-tl-content">
                    <div class="st-tl-head"><span class="st-tl-tag amber">Cập nhật</span></div>
                    <div class="st-tl-body">Đơn hàng đã được xử lý và giao thành công.</div>
                  </div>
                </div>

                <div class="st-tl-item">
                  <div class="st-tl-dot-col">
                    <div class="st-tl-dot green"><i class="bi bi-check2"></i></div>
                  </div>
                  <div class="st-tl-content">
                    <div class="st-tl-head"><span class="st-tl-tag green">Đã giao hàng</span></div>
                    <div class="st-tl-body">Đơn hàng đã hoàn tất giao đến khách hàng.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>
</div>

<script>
  (function () {
    const tabs = document.querySelectorAll('#orderTabs .st-order-tab-btn');
    const panels = document.querySelectorAll('.st-tab-panel');
    tabs.forEach((btn) => {
      btn.addEventListener('click', () => {
        tabs.forEach((b) => b.classList.remove('active'));
        panels.forEach((p) => p.classList.remove('active'));
        btn.classList.add('active');
        const target = document.getElementById(btn.dataset.tab);
        if (target) target.classList.add('active');
      });
    });
  })();
</script>

<?php include 'app/views/shares/footer.php'; ?>