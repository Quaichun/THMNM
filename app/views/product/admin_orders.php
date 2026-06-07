<?php
require_once 'app/helpers/SessionHelper.php';
SessionHelper::requireAdmin();
?>
<?php include 'app/views/shares/header.php'; ?>

<div class="st-page">
<div class="st-container">

  <!-- Page head -->
  <div class="st-page-head fade-up">
    <div>
      <h1>📊 Quản lý đơn hàng</h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> › <span>Quản lý đơn hàng</span>
      </div>
    </div>
  </div>

  <!-- ═══ KPI CARDS ═══ -->
  <div class="st-kpi-grid fade-up">

    <div class="st-kpi-card">
      <div class="st-kpi-icon" style="background:#e8f0fe;color:#1a56db">📦</div>
      <div class="st-kpi-info">
        <div class="st-kpi-label">Tổng đơn hàng</div>
        <div class="st-kpi-value">
          <?php echo number_format($stats->total_orders ?? 0); ?>
        </div>
        <div class="st-kpi-sub">Tất cả thời gian</div>
      </div>
    </div>

    <div class="st-kpi-card">
      <div class="st-kpi-icon" style="background:#d1fae5;color:#065f46">💰</div>
      <div class="st-kpi-info">
        <div class="st-kpi-label">Tổng doanh thu</div>
        <div class="st-kpi-value">
          <?php echo number_format($stats->total_revenue ?? 0, 0, ',', '.'); ?>₫
        </div>
        <div class="st-kpi-sub">Tất cả thời gian</div>
      </div>
    </div>

    <div class="st-kpi-card">
      <div class="st-kpi-icon" style="background:#fef3c7;color:#b45309">🗓️</div>
      <div class="st-kpi-info">
        <div class="st-kpi-label">Doanh thu tháng này</div>
        <div class="st-kpi-value">
          <?php echo number_format($stats->month_revenue ?? 0, 0, ',', '.'); ?>₫
        </div>
        <div class="st-kpi-sub">
          <?php echo $stats->month_orders ?? 0; ?> đơn hàng
        </div>
      </div>
    </div>

    <div class="st-kpi-card">
      <div class="st-kpi-icon" style="background:#fce7f3;color:#9d174d">⚡</div>
      <div class="st-kpi-info">
        <div class="st-kpi-label">Hôm nay</div>
        <div class="st-kpi-value">
          <?php echo number_format($stats->today_revenue ?? 0, 0, ',', '.'); ?>₫
        </div>
        <div class="st-kpi-sub">
          <?php echo $stats->today_orders ?? 0; ?> đơn hàng
        </div>
      </div>
    </div>

  </div>

  <!-- ═══ CHARTS ═══ -->
  <div class="st-chart-grid fade-up">

    <!-- Doanh thu theo tháng -->
    <div class="st-chart-card st-chart-main">
      <div class="st-chart-header">
        <div>
          <h3>📈 Doanh thu <?php echo $range === 'day' ? '30 ngày' : '12 tháng'; ?> gần nhất</h3>
          <p>Dữ liệu dựa trên đơn hàng đã thanh toán</p>
          <div class="st-range-toggle" style="margin-top:10px">
            <a href="/Product/myOrders?range=month" class="btn-range <?php echo $range === 'month' ? 'active' : ''; ?>">Theo tháng</a>
            <a href="/Product/myOrders?range=day" class="btn-range <?php echo $range === 'day' ? 'active' : ''; ?>">Theo ngày</a>
          </div>
        </div>
        <div class="st-chart-legend">
          <span class="st-legend-dot" style="background:#1a56db"></span> Doanh thu
          <span class="st-legend-dot" style="background:#10b981;margin-left:12px"></span> Đơn hàng
        </div>
      </div>
      <div class="st-chart-wrap">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <!-- Donut danh mục -->
    <div class="st-chart-card">
      <div class="st-chart-header">
        <div>
          <h3>🏷️ Theo danh mục</h3>
          <p>Tỷ lệ doanh thu</p>
        </div>
      </div>
      <div class="st-chart-wrap st-chart-wrap-sm">
        <canvas id="categoryChart"></canvas>
      </div>
    </div>

  </div>

  <!-- ═══ TOP SẢN PHẨM + DANH MỤC ═══ -->
  <div class="st-dash-bottom fade-up">

    <!-- Top sản phẩm -->
    <div class="st-top-products-card">
      <div class="st-chart-header">
        <h3>🏆 Top sản phẩm bán chạy</h3>
      </div>
      <div class="st-top-list">
        <?php if (empty($topProducts)): ?>
          <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:.88rem">
            Chưa có dữ liệu bán hàng
          </div>
        <?php else: ?>
          <?php foreach ($topProducts as $i => $tp): ?>
          <div class="st-top-item">
            <div class="st-top-rank
              <?php echo $i===0?'gold':($i===1?'silver':($i===2?'bronze':'')); ?>">
              <?php echo $i + 1; ?>
            </div>
            <div class="st-top-info">
              <div class="st-top-name">
                <?php echo htmlspecialchars($tp->name, ENT_QUOTES, 'UTF-8'); ?>
              </div>
              <div class="st-top-bar-wrap">
                <?php
                  $maxQty = (float)($topProducts[0]->total_qty ?: 1);
                  $pct    = round((float)$tp->total_qty / $maxQty * 100);
                ?>
                <div class="st-top-bar">
                  <div class="st-top-bar-fill" style="width:<?php echo $pct; ?>%"></div>
                </div>
                <span class="st-top-qty">
                  <?php echo number_format($tp->total_qty); ?> sp
                </span>
              </div>
            </div>
            <div class="st-top-revenue">
              <?php echo number_format($tp->total_revenue, 0, ',', '.'); ?>₫
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Doanh thu theo danh mục dạng list -->
    <div class="st-cat-revenue-card">
      <div class="st-chart-header">
        <h3>📂 Doanh thu theo danh mục</h3>
      </div>
      <div class="st-cat-revenue-list">
        <?php
          $totalRev  = array_sum(array_column($revenueBycat, 'revenue')) ?: 1;
          $catColors = ['#1a56db','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'];
        ?>
        <?php foreach ($revenueBycat as $i => $rc): ?>
          <?php $pct = round((float)$rc->revenue / $totalRev * 100); ?>
          <div class="st-cat-rev-item">
            <div class="st-cat-rev-dot"
                 style="background:<?php echo $catColors[$i % count($catColors)]; ?>">
            </div>
            <div class="st-cat-rev-info">
              <div class="st-cat-rev-name">
                <?php echo htmlspecialchars($rc->category, ENT_QUOTES, 'UTF-8'); ?>
              </div>
              <div class="st-cat-rev-bar-wrap">
                <div class="st-cat-rev-bar">
                  <div class="st-cat-rev-fill"
                       style="width:<?php echo $pct; ?>%;
                              background:<?php echo $catColors[$i % count($catColors)]; ?>">
                  </div>
                </div>
                <span class="st-cat-rev-pct"><?php echo $pct; ?>%</span>
              </div>
            </div>
            <div class="st-cat-rev-amount">
              <?php echo number_format($rc->revenue, 0, ',', '.'); ?>₫
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($revenueBycat)): ?>
          <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:.88rem">
            Chưa có dữ liệu
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ═══ BẢNG TẤT CẢ ĐƠN HÀNG ═══ -->
  <div class="st-table-card fade-up" style="margin-top:24px">
    <div class="st-table-header">
      <h1>🗒️ Tất cả đơn hàng</h1>
      <span style="opacity:.8;font-size:.85rem">
        <?php echo count($orders); ?> đơn hàng
      </span>
    </div>

    <?php if (empty($orders)): ?>
      <div class="st-empty" style="padding:40px">
        <div class="st-empty-icon">📋</div>
        <h3>Chưa có đơn hàng nào</h3>
      </div>
    <?php else: ?>
    <div style="overflow-x:auto">
      <table class="st-table">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Người đặt</th>
            <th>Điện thoại</th>
            <th>Số SP</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Ngày đặt</th>
            <th>Chi tiết</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <?php
            $status     = $o->status ?? 'pending';
            $statusMap  = [
              'pending'    => ['label' => '⏳ Chờ xử lý',    'class' => 'pending'],
              'processing' => ['label' => '⚙️ Đang xử lý',   'class' => 'processing'],
              'shipping'   => ['label' => '🚚 Đang giao',     'class' => 'shipping'],
              'delivered'  => ['label' => '✅ Đã giao',       'class' => 'done'],
              'cancelled'  => ['label' => '❌ Đã hủy',        'class' => 'cancelled'],
            ];
            $statusInfo = $statusMap[$status] ?? $statusMap['pending'];
          ?>
          <tr>
            <td>
              <strong style="color:var(--primary)">
                #<?php echo str_pad($o->id, 6, '0', STR_PAD_LEFT); ?>
              </strong>
            </td>
            <td><?php echo htmlspecialchars($o->name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($o->phone, ENT_QUOTES, 'UTF-8'); ?></td>
            <td style="text-align:center">
              <?php echo isset($o->item_count) ? $o->item_count : '—'; ?> sp
            </td>
            <td>
              <strong style="color:var(--primary)">
                <?php echo number_format($o->total_amount ?? 0, 0, ',', '.'); ?>₫
              </strong>
            </td>
            <td>
              <span class="st-order-status <?php echo $statusInfo['class']; ?>">
                <?php echo $statusInfo['label']; ?>
              </span>
            </td>
            <td style="white-space:nowrap;font-size:.82rem;color:var(--text-muted)">
              <?php echo date('d/m/Y H:i', strtotime($o->created_at)); ?>
            </td>
            <td>
              <a href="/Product/orderDetail/<?php echo $o->id; ?>"
                 class="btn btn-primary btn-sm">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartLabels  = <?php echo json_encode($chartLabels  ?? []); ?>;
const chartRevenue = <?php echo json_encode($chartRevenue ?? []); ?>;
const chartOrders  = <?php echo json_encode($chartOrders  ?? []); ?>;
const catLabels    = <?php echo json_encode(
    array_map(fn($r) => $r->category, $revenueBycat ?? [])
); ?>;
const catRevenue   = <?php echo json_encode(
    array_map(fn($r) => (float)$r->revenue, $revenueBycat ?? [])
); ?>;

const COLORS = ['#1a56db','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];

/* ── Biểu đồ cột + line: doanh thu theo tháng ── */
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
  new Chart(revenueCtx, {
    type: 'bar',
    data: {
      labels: chartLabels.length ? chartLabels : ['Chưa có dữ liệu'],
      datasets: [
        {
          label: 'Doanh thu (₫)',
          data: chartRevenue.length ? chartRevenue : [0],
          backgroundColor: 'rgba(26,86,219,.18)',
          borderColor: '#1a56db',
          borderWidth: 2,
          borderRadius: 8,
          yAxisID: 'y',
        },
        {
          label: 'Số đơn',
          data: chartOrders.length ? chartOrders : [0],
          type: 'line',
          borderColor: '#10b981',
          backgroundColor: 'rgba(16,185,129,.1)',
          borderWidth: 2.5,
          pointBackgroundColor: '#10b981',
          pointRadius: 4,
          tension: 0.4,
          yAxisID: 'y1',
          fill: true,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ctx.datasetIndex === 0
              ? ' Doanh thu: ' + parseInt(ctx.raw).toLocaleString('vi-VN') + '₫'
              : ' Đơn hàng: ' + ctx.raw
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 }, color: '#6b7280' }
        },
        y: {
          position: 'left',
          grid: { color: 'rgba(0,0,0,.05)' },
          ticks: {
            font: { size: 10 }, color: '#6b7280',
            callback: v => (v >= 1000000
              ? (v/1000000).toFixed(1)+'M₫'
              : (v/1000).toFixed(0)+'K₫')
          }
        },
        y1: {
          position: 'right',
          grid: { drawOnChartArea: false },
          ticks: { font: { size: 10 }, color: '#10b981' }
        }
      }
    }
  });
}

/* ── Biểu đồ donut: theo danh mục ── */
const catCtx = document.getElementById('categoryChart');
if (catCtx) {
  new Chart(catCtx, {
    type: 'doughnut',
    data: {
      labels: catLabels.length ? catLabels : ['Chưa có dữ liệu'],
      datasets: [{
        data: catRevenue.length ? catRevenue : [1],
        backgroundColor: COLORS,
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 8,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { font:{size:11}, padding:12, usePointStyle:true }
        },
        tooltip: {
          callbacks: {
            label: ctx => {
              const total = ctx.dataset.data.reduce((a,b)=>a+b,0)||1;
              const pct   = Math.round(ctx.raw/total*100);
              return ` ${ctx.label}: ${parseInt(ctx.raw).toLocaleString('vi-VN')}₫ (${pct}%)`;
            }
          }
        }
      }
    }
  });
}
</script>

<?php include 'app/views/shares/footer.php'; ?>