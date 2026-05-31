<?php include 'app/views/shares/header.php'; ?>

<!-- ── Inject dữ liệu cho autocomplete ── -->
<script>
window.ST_PRODUCTS = <?php echo json_encode(array_map(function($p) {
    return [
        'id'    => (int)$p->id,
        'name'  => htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'),
        'price' => (int)$p->price,
        'cat'   => htmlspecialchars($p->category_name ?? '', ENT_QUOTES, 'UTF-8'),
        'image' => $p->image ?? ''
    ];
}, $products), JSON_UNESCAPED_UNICODE); ?>;
</script>

<!-- ═══ BANNER CAROUSEL — map ảnh sản phẩm thật ═══ -->
<?php
$bannerProducts = array_slice($products, 0, 4);
$bannerConfigs  = [
    ['class'=>'st-carousel-slide-1','badge'=>'🔥 Nổi bật',         'btn'=>'Mua ngay'],
    ['class'=>'st-carousel-slide-2','badge'=>'💚 Bán chạy',        'btn'=>'Xem ngay'],
    ['class'=>'st-carousel-slide-3','badge'=>'⭐ Được yêu thích',  'btn'=>'Khám phá'],
    ['class'=>'st-carousel-slide-4','badge'=>'⚡ Flash Sale',       'btn'=>'Mua ngay'],
];
?>
<div class="st-carousel fade-up">
  <div class="st-carousel-track">

    <?php if (!empty($bannerProducts)):
      foreach ($bannerProducts as $i => $bp):
        $cfg = $bannerConfigs[$i % 4];
    ?>
    <div class="st-carousel-slide <?php echo $cfg['class']; ?>">

      <div class="st-slide-text">
        <span class="st-slide-badge"><?php echo $cfg['badge']; ?></span>
        <h2><?php echo htmlspecialchars($bp->name, ENT_QUOTES, 'UTF-8'); ?></h2>
        <p>
          <?php if (!empty($bp->category_name)): ?>
            <span style="opacity:.8;"><?php echo htmlspecialchars($bp->category_name, ENT_QUOTES, 'UTF-8'); ?></span>
            &nbsp;·&nbsp;
          <?php endif; ?>
          <strong style="font-size:1.1em;">
            <?php echo number_format($bp->price, 0, ',', '.'); ?>₫
          </strong>
        </p>
        <a href="/Product/show/<?php echo $bp->id; ?>" class="btn-slide">
          <?php echo $cfg['btn']; ?> <i class="fa-solid fa-arrow-right" style="font-size:.8em;"></i>
        </a>
      </div>

      <!-- Ảnh sản phẩm thật -->
      <div class="st-slide-img-wrap">
        <?php if (!empty($bp->image)): ?>
          <img class="st-slide-product-img"
               src="/<?php echo htmlspecialchars($bp->image, ENT_QUOTES, 'UTF-8'); ?>"
               alt="<?php echo htmlspecialchars($bp->name, ENT_QUOTES, 'UTF-8'); ?>">
        <?php else: ?>
          <div class="st-slide-img">📦</div>
        <?php endif; ?>
      </div>

    </div>
    <?php endforeach;
    else: ?>
    <!-- Fallback khi chưa có SP -->
    <div class="st-carousel-slide st-carousel-slide-1">
      <div class="st-slide-text">
        <span class="st-slide-badge">🛍️ Chào mừng</span>
        <h2>Sản phẩm công nghệ<br>Chất lượng – Giá tốt</h2>
        <p>Thêm sản phẩm đầu tiên vào shop của bạn!</p>
        <a href="/Product/add" class="btn-slide">+ Thêm sản phẩm</a>
      </div>
      <div class="st-slide-img">🛍️</div>
    </div>
    <?php endif; ?>

  </div>

  <button class="st-carousel-btn prev" aria-label="Trước"><i class="bi bi-chevron-left"></i></button>
  <button class="st-carousel-btn next" aria-label="Tiếp"><i class="bi bi-chevron-right"></i></button>

  <div class="st-carousel-dots">
    <?php $dotCount = max(1, min(count($bannerProducts), 4));
    for ($i = 0; $i < $dotCount; $i++): ?>
      <button class="st-dot <?php echo $i === 0 ? 'active' : ''; ?>"></button>
    <?php endfor; ?>
  </div>
</div>

<!-- ═══ SHOP LAYOUT ═══ -->
<div class="st-shop-layout">

  <!-- ─── Sidebar ─── -->
  <aside class="st-sidebar fade-up">
    <p class="st-sidebar-title">Bộ lọc</p>

    <h5><i class="bi bi-grid-3x3-gap" style="color:var(--primary)"></i> Danh mục</h5>
    <ul class="st-cat-list">
      <li>
        <label><input type="radio" name="cat-filter" value="all" checked> Tất cả</label>
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

    <!-- Dual range slider -->
    <?php
      $maxPrice = 50000000;
      foreach ($products as $p) {
        if ((int)$p->price > $maxPrice) $maxPrice = (int)$p->price;
      }
    ?>
    <div class="st-price-slider-wrap">
      <h5 style="margin-top:20px;">
        <i class="bi bi-currency-dollar" style="color:var(--primary)"></i> Khoảng giá
      </h5>
      <div class="st-price-slider-labels">
        <span id="labelMin">0₫</span>
        <span id="labelMax"><?php echo number_format($maxPrice,0,',','.'); ?>₫</span>
      </div>
      <div class="st-range-track">
        <div class="st-range-fill"></div>
        <input class="st-range-input" type="range" id="rangeMin"
               min="0" max="<?php echo $maxPrice; ?>" value="0" step="100000">
        <input class="st-range-input" type="range" id="rangeMax"
               min="0" max="<?php echo $maxPrice; ?>" value="<?php echo $maxPrice; ?>" step="100000">
      </div>
      <button class="btn-filter" id="btnPriceFilter">
        <i class="bi bi-funnel"></i> Áp dụng
      </button>
    </div>

    <hr style="margin:20px 0;border-color:var(--border);">
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

    <!-- Product grid -->
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

  <?php if (SessionHelper::isAdmin()): ?>
    <a href="/Product/edit/<?php echo $p->id; ?>"
       class="btn btn-warning btn-sm">
      <i class="bi bi-pencil"></i>
    </a>
    <a href="/Product/delete/<?php echo $p->id; ?>"
       class="btn btn-danger btn-sm btn-delete-confirm">
      <i class="bi bi-trash"></i>
    </a>
  <?php endif; ?>

  <?php if (SessionHelper::isLoggedIn() && !SessionHelper::isAdmin()): ?>
    <a href="/Product/addToCart/<?php echo $p->id; ?>"
       class="btn btn-success btn-sm">
      <i class="bi bi-cart-plus"></i> Thêm giỏ
    </a>
  <?php endif; ?>
</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Không tìm thấy (ẩn mặc định) -->
    <div id="noResultMsg" style="display:none;" class="st-empty">
      <div class="st-empty-icon">🔍</div>
      <h3>Không tìm thấy sản phẩm phù hợp</h3>
      <p>Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
    </div>

    <!-- Nút xem thêm — JS sẽ inject vào đây -->
    <div id="loadMoreWrap" style="width:100%;display:flex;justify-content:center;padding:8px 0 16px;"></div>

    <?php endif; ?>
  </div>

</div><!-- /st-shop-layout -->

<?php include 'app/views/shares/footer.php'; ?>