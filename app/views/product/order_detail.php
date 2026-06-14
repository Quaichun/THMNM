<?php include 'app/views/shares/header.php'; ?>

<?php
$statusMap = [
  'pending'    => ['label' => 'Chờ xác nhận', 'color' => '#b45309'],
  'processing' => ['label' => 'Đang xử lý', 'color' => '#1d4ed8'],
  'shipping'   => ['label' => 'Đang giao hàng', 'color' => '#0369a1'],
  'delivered'  => ['label' => 'Đã giao hàng', 'color' => '#15803d'],
  'cancelled'  => ['label' => 'Đã hủy', 'color' => '#b91c1c'],
];
$currentStatus = $order->status ?? 'pending';
if (!isset($statusMap[$currentStatus])) $currentStatus = 'pending';
?>

<div class="st-page">
<div class="st-container">
  <div class="st-page-head fade-up">
    <div>
      <h1>Chi tiết đơn hàng #<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> ›
        <a href="/Product/myOrders">Đơn hàng</a> ›
        <span>Chi tiết</span>
      </div>
    </div>
    <a href="/Product/myOrders" class="btn btn-secondary">← Quay lại</a>
  </div>

  <div style="display:grid;grid-template-columns:1.1fr .9fr;gap:24px;align-items:start">
    <div class="st-table-card fade-up">
      <div class="st-table-header"><h1>Sản phẩm đã đặt</h1></div>
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
          <?php $grandTotal = 0; foreach ($orderDetails as $d): $sub = $d->price * $d->quantity; $grandTotal += $sub; ?>
          <tr>
            <td style="width:60px">
              <?php if (!empty($d->product_image)): ?>
                <img src="/<?php echo htmlspecialchars($d->product_image, ENT_QUOTES, 'UTF-8'); ?>"
                     style="width:54px;height:54px;object-fit:cover;border-radius:8px"
                     alt="<?php echo htmlspecialchars($d->product_name, ENT_QUOTES, 'UTF-8'); ?>">
              <?php else: ?>
                <div style="width:54px;height:54px;background:var(--bg-page);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.4rem">📦</div>
              <?php endif; ?>
            </td>
            <td style="font-weight:700"><?php echo htmlspecialchars($d->product_name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo number_format($d->price, 0, ',', '.'); ?>₫</td>
            <td><?php echo (int)$d->quantity; ?></td>
            <td style="font-weight:800;color:var(--primary)"><?php echo number_format($sub, 0, ',', '.'); ?>₫</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" style="text-align:right;font-weight:700;padding:16px 20px">Tổng thanh toán</td>
            <td style="font-weight:800;font-size:1.1rem;color:var(--primary);padding:16px 20px"><?php echo number_format($grandTotal, 0, ',', '.'); ?>₫</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="fade-up">
      <div class="st-cart-summary-box" style="position:static;margin-bottom:20px">
        <div class="st-summary-title"><i class="bi bi-person-fill"></i> Thông tin khách hàng</div>
        <div class="st-summary-rows">
          <div class="st-summary-row"><span>Người nhận</span><strong><?php echo htmlspecialchars($order->name, ENT_QUOTES, 'UTF-8'); ?></strong></div>
          <div class="st-summary-row"><span>Điện thoại</span><strong><?php echo htmlspecialchars($order->phone, ENT_QUOTES, 'UTF-8'); ?></strong></div>
          <div class="st-summary-row"><span>Email</span><strong><?php echo htmlspecialchars($order->email ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong></div>
          <div class="st-summary-row"><span>Thanh toán</span><strong><?php echo (($order->payment_method ?? 'cod') === 'bank') ? 'CHUYỂN KHOẢN/QR' : strtoupper(htmlspecialchars($order->payment_method ?? 'cod', ENT_QUOTES, 'UTF-8')); ?></strong></div>
          <div class="st-summary-row"><span>Ngày đặt</span><strong><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></strong></div>
        </div>
        <div class="st-summary-divider"></div>
        <div style="font-size:.88rem;color:var(--text-muted);margin-bottom:4px">Địa chỉ giao hàng</div>
        <div style="font-weight:700"><?php echo htmlspecialchars($order->address, ENT_QUOTES, 'UTF-8'); ?></div>
      </div>

      <div class="st-cart-summary-box" style="position:static">
        <div class="st-summary-title"><i class="bi bi-truck"></i> Trạng thái giao hàng</div>
        <div style="display:inline-block;padding:7px 12px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;font-weight:700;color:<?php echo $statusMap[$currentStatus]['color']; ?>;margin-bottom:14px">
          <?php echo $statusMap[$currentStatus]['label']; ?>
        </div>
        <?php if (SessionHelper::isAdmin()): ?>
        <form id="updateStatusForm">
          <label class="form-label">Cập nhật trạng thái</label>
          <select name="status" id="statusSelect" class="form-control" style="margin-bottom:14px" required>
            <option value="pending" <?php if ($currentStatus === 'pending') echo 'selected'; ?>>Chờ xác nhận</option>
            <option value="processing" <?php if ($currentStatus === 'processing') echo 'selected'; ?>>Đang xử lý</option>
            <option value="shipping" <?php if ($currentStatus === 'shipping') echo 'selected'; ?>>Đang giao hàng</option>
            <option value="delivered" <?php if ($currentStatus === 'delivered') echo 'selected'; ?>>Đã giao hàng</option>
            <option value="cancelled" <?php if ($currentStatus === 'cancelled') echo 'selected'; ?>>Đã hủy</option>
          </select>
          <button type="submit" class="btn btn-primary w-100" id="btnSaveStatus">
            <i class="bi bi-check2-circle"></i> Lưu trạng thái
          </button>
        </form>

        <script>
        document.getElementById('updateStatusForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSaveStatus');
            const statusSelect = document.getElementById('statusSelect');
            const status = statusSelect.value;
            const orderId = <?php echo (int)$order->id; ?>;
            const statusMap = <?php echo json_encode($statusMap); ?>;

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang lưu...';

            try {
                const res = await apiFetch(`/api/order/update/${orderId}`, {
                    method: 'PUT',
                    body: JSON.stringify({ status })
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Đã cập nhật trạng thái!', '🚚');
                    const badge = document.querySelector('[style*="background:#f8fafc"]');
                    if (badge && statusMap[status]) {
                        badge.textContent = statusMap[status].label;
                        badge.style.color = statusMap[status].color;
                    }
                } else {
                    alert(result.message || 'Lỗi cập nhật');
                }
            } catch (e) { alert('Lỗi kết nối'); } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle"></i> Lưu trạng thái';
            }
        });
        </script>

        <?php else: ?>
          <div style="font-size:.9rem;color:var(--text-muted)">
            Bạn chỉ có quyền xem trạng thái giao hàng của đơn này.
          </div>
        <?php endif; ?>
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