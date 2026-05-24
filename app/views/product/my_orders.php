<?php include 'app/views/shares/header.php'; ?>

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
          <tr>
            <td><strong>#<?php echo str_pad($o->id, 6, '0', STR_PAD_LEFT); ?></strong></td>
            <td><?php echo htmlspecialchars($o->name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($o->phone, ENT_QUOTES, 'UTF-8'); ?></td>
            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?php echo htmlspecialchars($o->address, ENT_QUOTES, 'UTF-8'); ?>
            </td>
            <td><?php echo date('d/m/Y H:i', strtotime($o->created_at)); ?></td>
            <td>
              <span class="st-order-status processing">⚙️ Đang xử lý</span>
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