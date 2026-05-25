<?php include 'app/views/shares/header.php'; ?>

<?php
$statusMap = [
  'pending'    => ['label' => 'Chờ xác nhận', 'bg' => '#fff7ed', 'color' => '#b45309'],
  'processing' => ['label' => 'Đang xử lý', 'bg' => '#eff6ff', 'color' => '#1d4ed8'],
  'shipping'   => ['label' => 'Đang giao hàng', 'bg' => '#ecfeff', 'color' => '#0e7490'],
  'delivered'  => ['label' => 'Đã giao hàng', 'bg' => '#ecfdf5', 'color' => '#15803d'],
  'cancelled'  => ['label' => 'Đã hủy', 'bg' => '#fef2f2', 'color' => '#b91c1c'],
];
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

  <?php if (empty($orders)): ?>
    <div class="st-cart-empty fade-up">
      <div class="st-cart-empty-icon">📋</div>
      <h2>Chưa có đơn hàng nào</h2>
      <p>Hãy mua sắm để tạo đơn hàng đầu tiên!</p>
      <a href="/Product" class="btn btn-primary btn-lg">🛍️ Mua sắm ngay</a>
    </div>
  <?php else: ?>
  <div class="st-table-card fade-up">
    <div class="st-table-header">
      <h1>Danh sách đơn hàng</h1>
      <span style="font-size:.85rem;opacity:.8"><?php echo count($orders); ?> đơn hàng</span>
    </div>
    <div style="overflow-x:auto">
      <table class="st-table">
        <thead>
          <tr>
            <th>Mã đơn</th>
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
          <?php
            $key = $o->status ?? 'pending';
            if (!isset($statusMap[$key])) $key = 'pending';
            $st = $statusMap[$key];
          ?>
          <tr>
            <td><strong>#<?php echo str_pad($o->id, 6, '0', STR_PAD_LEFT); ?></strong></td>
            <td><?php echo htmlspecialchars($o->name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($o->phone, ENT_QUOTES, 'UTF-8'); ?></td>
            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?php echo htmlspecialchars($o->address, ENT_QUOTES, 'UTF-8'); ?>
            </td>
            <td><?php echo date('d/m/Y H:i', strtotime($o->created_at)); ?></td>
            <td>
              <span style="display:inline-block;padding:6px 10px;border-radius:999px;background:<?php echo $st['bg']; ?>;color:<?php echo $st['color']; ?>;font-weight:700;font-size:.8rem;">
                <?php echo htmlspecialchars($st['label'], ENT_QUOTES, 'UTF-8'); ?>
              </span>
            </td>
            <td>
              <a href="/Product/orderDetail/<?php echo $o->id; ?>"
                 class="btn btn-primary btn-sm">
                <i class="bi bi-eye"></i> Xem
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
