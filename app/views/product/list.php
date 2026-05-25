<?php include 'app/views/shares/header.php'; ?>

<!-- Truyền danh sách sản phẩm cho JS autocomplete -->
<script>
window.ST_PRODUCTS = <?php echo json_encode(array_map(function($p) {
    return [
        'id'    => $p->id,
        'name'  => $p->name,
        'price' => $p->price,
        'cat'   => $p->category_name ?? ''
    ];
}, $products)); ?>;
</script>

<!-- ═══ CAROUSEL BANNER ═══ -->
<div class="st-carousel">
  <div class="st-carousel-track">

    <div class="st-carousel-slide st-carousel-slide-1">
      <div class="st-slide-text">
        <span class="st-slide-badge">🔥 Mới nhất 2025</span>
        <h2>Sản phẩm công nghệ<br>Chất lượng – Giá tốt</h2>
        <p>Khám phá ngay những sản phẩm công nghệ mới nhất</p>
        <a href="/Product/add" class="btn-slide">+ Thêm sản phẩm</a>
      </div>
      <div class="st-slide-img">📱</div>
    </div>

    <div class="st-carousel-slide st-carousel-slide-2">
      <div class="st-slide-text">
        <span class="st-slide-badge">💻 Laptop & PC</span>
        <h2>Máy tính hiệu năng cao<br>Giá cạnh tranh</h2>
        <p>Làm việc mượt mà, giải trí đỉnh cao</p>
        <a href="/Product" class="btn-slide">Xem ngay</a>
      </div>
      <div class="st-slide-img">💻</div>
    </div>

    <div class="st-carousel-slide st-carousel-slide-3">
      <div class="st-slide-text">
        <span class="st-slide-badge">🎧 Phụ kiện</span>
        <h2>Phụ kiện chính hãng<br>Bảo hành dài hạn</h2>
        <p>Nâng tầm trải nghiệm công nghệ của bạn</p>
        <a href="/Product" class="btn-slide">Khám phá</a>
      </div>
      <div class="st-slide-img">🎧</div>
    </div>

    <div class="st-carousel-slide st-carousel-slide-4">
      <div class="st-slide-text">
        <span class="st-slide-badge">⚡ Flash Sale</span>
        <h2>Ưu đãi hôm nay<br>Giảm đến 50%</h2>
        <p>Số lượng có hạn – Đặt hàng ngay!</p>
        <a href="/Product" class="btn-slide">Mua ngay</a>
      </div>
      <div class="st-slide-img">🛒</div>
    </div>

  </div>

  <!-- Nút prev/next -->
  <button class="st-carousel-btn prev">&#8249;</button>
  <button class="st-carousel-btn next">&#8250;</button>

  <!-- Dots -->
  <div class="st-carousel-dots">
    <button class="st-dot active"></button>
    <button class="st-dot"></button>
    <button class="st-dot"></button>
    <button class="st-dot"></button>
  </div>
</div>

<!-- ═══ SHOP LAYOUT ═══ -->
<div class="st-shop-layout">

  <!-- ─── Sidebar ─── -->
  <aside class="st-sidebar fade-up">

    <p class="st-sidebar-title">Bộ lọc</p>

    <!-- Category filter -->
    <h5><i class="bi bi-grid-3x3-gap" style="color:var(--primary)"></i> Danh mục</h5>
    <ul class="st-cat-list">
      <li>
        <label>
          <input type="radio" name="cat-filter" value="all" checked> Tất cả
        </label>
      </li>
      <?php
        $cats = [];
        foreach ($products as $p) {
          if ($p->category_name && !in_array($p->category_name, $cats))
            $cats[] = $p->category_name;
        }
        foreach ($cats as $cat):
      ?>
      <li>
        <label>
          <input type="radio" name="cat-filter"
            value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
        </label>
      </li>
      <?php endforeach; ?>
    </ul>

    <!-- ─── Dual Range Slider giá ─── -->
    <div class="st-price-slider-wrap">
      <h5 style="margin-top:20px;">
        <i class="bi bi-currency-dollar" style="color:var(--primary)"></i> Khoảng giá
      </h5>

      <div class="st-price-slider-labels">
        <span id="labelMin">0₫</span>
        <span id="labelMax">50.000.000₫</span>
      </div>

      <div class="st-range-track">
        <div class="st-range-fill" id="rangeFill"></div>
        <input class="st-range-input" type="range"
               id="rangeMin" min="0" max="50000000" value="0" step="100000">
        <input class="st-range-input" type="range"
               id="rangeMax" min="0" max="50000000" value="50000000" step="100000">
      </div>

      <button class="btn-filter" id="btnPriceFilter">
        <i class="bi bi-funnel"></i> Áp dụng
      </button>
    </div>

    <hr style="margin:20px 0; border-color:var(--border);">
    <a href="/Product/add" class="btn btn-success w-100">
      <i class="bi bi-plus-circle"></i> Thêm sản phẩm
    </a>

  </aside>

  <!-- ─── Main content ─── -->
  <div>

    <!-- Sort bar -->
    <div class="st-sort-bar">
      <span><?php echo count($products); ?> sản phẩm</span>
      <select class="st-sort-select" id="sortProducts">
        <option value="">Mặc định</option>
        <option value="asc">Giá: Thấp → Cao</option>
        <option value="desc">Giá: Cao → Thấp</option>
      </select>
    </div>

    <!-- Grid -->
    <?php if (empty($products)): ?>
      <div class="st-empty">
        <div class="st-empty-icon">📦</div>
        <h3>Chưa có sản phẩm nào</h3>
        <p>Hãy thêm sản phẩm đầu tiên của bạn!</p>
        <a href="/Product/add" class="btn btn-primary mt-3">+ Thêm sản phẩm</a>
      </div>
    <?php else: ?>
    <div class="st-product-grid" id="productGrid">
      <?php foreach ($products as $p): ?>
      <div class="st-card fade-up"
           data-price="<?php echo (int)$p->price; ?>"
           data-name="<?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?>"
           data-cat="<?php echo htmlspecialchars($p->category_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <a href="/Product/show/<?php echo $p->id; ?>">
          <?php if ($p->image): ?>
            <img class="st-card-img" loading="lazy"
                 src="/<?php echo htmlspecialchars($p->image, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?>">
          <?php else: ?>
            <div class="st-card-img-placeholder">📦</div>
          <?php endif; ?>
        </a>

        <div class="st-card-body">
          <?php if ($p->category_name): ?>
            <span class="st-card-cat">
              <?php echo htmlspecialchars($p->category_name, ENT_QUOTES, 'UTF-8'); ?>
            </span>
          <?php endif; ?>

          <div class="st-card-name">
            <a href="/Product/show/<?php echo $p->id; ?>" style="color:inherit;">
              <?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?>
            </a>
          </div>

          <div class="st-card-price">
            <?php echo number_format($p->price, 0, ',', '.'); ?>₫
          </div>

          <div class="st-card-actions">
            <a href="/Product/show/<?php echo $p->id; ?>"
               class="btn btn-primary btn-sm">
              <i class="bi bi-eye"></i> Xem
            </a>
            <a href="/Product/edit/<?php echo $p->id; ?>"
               class="btn btn-warning btn-sm">
              <i class="bi bi-pencil"></i>
            </a>
            <a href="/Product/addToCart/<?php echo $p->id; ?>"
               class="btn btn-success btn-sm">
              <i class="bi bi-cart-plus"></i>
            </a>
            <a href="/Product/delete/<?php echo $p->id; ?>"
               class="btn btn-danger btn-sm btn-delete-confirm">
              <i class="bi bi-trash"></i>
            </a>
          </div>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

    <!-- Thông báo không tìm thấy -->
    <div id="noResultMsg" style="display:none;" class="st-empty">
      <div class="st-empty-icon">🔍</div>
      <h3>Không tìm thấy sản phẩm phù hợp</h3>
      <p>Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
    </div>

    <?php endif; ?>

  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
