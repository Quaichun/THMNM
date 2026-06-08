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
        <a href="/api/product/<?php echo $product->id; ?>"
           class="btn btn-danger btn-sm btn-delete-confirm st-api-delete-product">
          <i class="bi bi-trash"></i>
        </a>
      <?php endif; ?>

    </div>

  </div>
</div>

<!-- Specs and Reviews -->
<div class="st-product-extra fade-up" style="margin-top: 40px;">
  
  <div class="st-extra-grid">
    <!-- Left: Specifications -->
    <div class="st-specs-card">
      <h3 class="st-section-title"><i class="bi bi-cpu"></i> Thông số kỹ thuật</h3>
      <table class="st-specs-table">
        <tbody>
          <?php if (!empty($specs)): ?>
            <?php foreach ($specs as $spec): ?>
              <tr>
                <td class="spec-name"><?php echo htmlspecialchars($spec->spec_name, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="spec-value"><?php echo htmlspecialchars($spec->spec_value, ENT_QUOTES, 'UTF-8'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="2" style="text-align: center; padding: 20px; color: #888;">
                Chưa có thông số kỹ thuật cho sản phẩm này.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Right: Reviews -->
    <div id="reviews" class="st-reviews-section">
      <h3 class="st-section-title"><i class="bi bi-chat-left-text"></i> Đánh giá & Nhận xét</h3>
      
      <!-- Rating Summary (TGDĐ Style) -->
      <?php if ($ratingStats && $ratingStats->total > 0): ?>
      <div class="st-rating-summary">
        <div class="st-rating-avg">
          <div class="avg-score"><?php echo number_format($ratingStats->average, 1); ?> <i class="bi bi-star-fill"></i></div>
          <div class="total-count"><?php echo $ratingStats->total; ?> đánh giá</div>
        </div>
        <div class="st-rating-bars">
          <?php for($i=5; $i>=1; $i--): 
            $starKey = "star".$i;
            $count = $ratingStats->$starKey;
            $percent = ($count / $ratingStats->total) * 100;
          ?>
          <div class="bar-item">
            <span class="star-num"><?php echo $i; ?> <i class="bi bi-star-fill"></i></span>
            <div class="bar-bg"><div class="bar-fill" style="width: <?php echo $percent; ?>%"></div></div>
            <span class="percent-num"><?php echo round($percent); ?>%</span>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Review Image Gallery -->
      <?php 
        $reviewImages = array_filter(array_map(fn($r) => $r->image, $reviews));
        if (!empty($reviewImages)):
      ?>
      <div class="st-review-gallery">
        <p>Ảnh từ khách hàng (<?php echo count($reviewImages); ?>):</p>
        <div class="gallery-scroll">
          <?php foreach ($reviewImages as $img): ?>
            <img src="/<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="Review image" onclick="window.open(this.src)">
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>

      <!-- Review Form -->
      <?php if (SessionHelper::isLoggedIn()): ?>
        <form action="/Product/submitReview" method="POST" enctype="multipart/form-data" class="st-review-form">
          <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
          <div class="st-rating-input">
            <div class="st-stars">
              <?php for($i=5; $i>=1; $i--): ?>
                <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" <?php echo $i==5?'checked':''; ?>>
                <label for="star<?php echo $i; ?>"><i class="bi bi-star-fill"></i></label>
              <?php endfor; ?>
            </div>
          </div>
          <textarea name="comment" placeholder="Bạn thấy sản phẩm này thế nào?..." required></textarea>
          <div class="st-form-footer">
            <label class="btn-upload">
              <i class="bi bi-camera"></i> Gửi ảnh thực tế
              <input type="file" name="review_image" accept="image/*" onchange="previewImg(this)">
            </label>
            <div id="imgPreview"></div>
            <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
          </div>
        </form>
        <script>
          function previewImg(input) {
            const preview = document.getElementById('imgPreview');
            preview.innerHTML = '';
            if (input.files && input.files[0]) {
              const reader = new FileReader();
              reader.onload = e => preview.innerHTML = `<img src="${e.target.result}" style="width:50px;height:50px;border-radius:4px;object-fit:cover">`;
              reader.readAsDataURL(input.files[0]);
            }
          }
        </script>
      <?php else: ?>
        <p class="st-login-hint">Vui lòng <a href="/Account/login">đăng nhập</a> để gửi đánh giá.</p>
      <?php endif; ?>

      <!-- Review List -->
      <div class="st-review-list">
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $rev): ?>
            <div class="st-review-item">
              <div class="st-review-header">
                <div class="rev-info">
                  <div class="rev-name">
                    <?php echo htmlspecialchars($rev->fullname, ENT_QUOTES, 'UTF-8'); ?>
                    <span class="verified-tag"><i class="bi bi-check-circle-fill"></i> Đã mua tại ShopTech</span>
                  </div>
                  <div class="rev-meta">
                    <span class="rev-stars">
                      <?php for($i=1; $i<=5; $i++): ?>
                        <i class="bi bi-star-fill <?php echo $i<=$rev->rating ? 'active' : ''; ?>"></i>
                      <?php endfor; ?>
                    </span>
                    <span class="rev-date"><i class="bi bi-clock"></i> <?php echo date('d/m/Y', strtotime($rev->created_at)); ?></span>
                  </div>
                </div>
              </div>
              <div class="st-review-body">
                <div class="rev-text"><?php echo nl2br(htmlspecialchars($rev->comment, ENT_QUOTES, 'UTF-8')); ?></div>
                <?php if ($rev->image): ?>
                  <div class="rev-img-wrap">
                    <img src="/<?php echo htmlspecialchars($rev->image, ENT_QUOTES, 'UTF-8'); ?>" alt="User upload" onclick="window.open(this.src)">
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align: center; color: #888; padding: 20px;">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<?php else: ?>
  <div class="alert alert-danger">Không tìm thấy sản phẩm!</div>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>
