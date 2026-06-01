<?php include 'app/views/shares/header.php'; ?>

<?php if ($product): ?>
<script>
window.ST_CURRENT_PRODUCT = {
  id:    <?php echo (int)$product->id; ?>,
  name:  <?php echo json_encode(htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8')); ?>,
  price: <?php echo (int)$product->price; ?>,
  image: <?php echo json_encode($product->image ?? ''); ?>,
  cat:   <?php echo json_encode(htmlspecialchars($product->category_name ?? '', ENT_QUOTES, 'UTF-8')); ?>
};
</script>
<?php endif; ?>

<!-- Breadcrumb -->
<div class="st-page-head fade-up">
  <div>
    <div class="st-breadcrumb">
      <a href="/Product/">Trang chủ</a>
      <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
      <a href="/Product/list">Sản phẩm</a>
      <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
      <span><?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
  </div>
  <a href="/Product/list" class="btn btn-outline btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<?php if ($product): ?>

<div class="st-detail-layout fade-up">

  <!-- Left: Image -->
  <div>
    <div class="st-detail-img-wrap">
      <?php if ($product->image): ?>
        <img src="/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>"
             alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>">
      <?php else: ?>
        <div class="st-detail-img-placeholder">📦</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right: Info -->
  <div class="st-detail-info">

    <?php if ($product->category_name): ?>
      <span class="st-detail-cat">
        <?php echo htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8'); ?>
      </span>
    <?php endif; ?>

    <h1 class="st-detail-name">
      <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
    </h1>

    <div class="st-detail-price">
      <?php echo number_format($product->price, 0, ',', '.'); ?>₫
    </div>

    <div class="st-detail-desc">
      <?php echo nl2br(htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8')); ?>
    </div>

    <!-- Trust badges -->
    <div class="st-trust-badges">
      <div class="st-badge-item"><span>🚚</span> Miễn phí vận chuyển từ 500k</div>
      <div class="st-badge-item"><span>🔄</span> Đổi trả dễ dàng trong 7 ngày</div>
      <div class="st-badge-item"><span>🛡️</span> Thanh toán an toàn</div>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-3 align-items-center" style="margin-top:8px;flex-wrap:wrap;">

      <?php if (SessionHelper::isLoggedIn()): ?>
        <a href="/Product/addToCart/<?php echo $product->id; ?>"
           class="btn btn-primary btn-lg">
          <i class="bi bi-cart-plus"></i> Thêm vào giỏ hàng
        </a>
      <?php else: ?>
        <a href="/Account/login" class="btn btn-primary btn-lg">
          <i class="bi bi-box-arrow-in-right"></i> Đăng nhập để mua hàng
        </a>
      <?php endif; ?>

      <?php if (SessionHelper::isAdmin()): ?>
        <a href="/Product/edit/<?php echo $product->id; ?>"
           class="btn btn-warning btn-lg">
          <i class="bi bi-pencil"></i> Sửa
        </a>
        <a href="/Product/delete/<?php echo $product->id; ?>"
           class="btn btn-danger btn-sm btn-delete-confirm">
          <i class="bi bi-trash"></i>
        </a>
      <?php endif; ?>

    </div>

  </div>
</div>

<?php else: ?>
  <div class="alert alert-danger">Không tìm thấy sản phẩm!</div>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>