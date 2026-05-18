<?php include 'app/views/shares/header.php'; ?>

<div class="st-page-head fade-up">
  <div class="st-breadcrumb">
    <a href="/Product/">Trang chủ</a>
    <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
    <span>Thêm sản phẩm mới</span>
  </div>
</div>

<div class="st-form-page fade-up">
  <div class="st-form-card">

    <div class="st-form-header">
      <h1><i class="bi bi-plus-circle"></i> Thêm sản phẩm mới</h1>
      <p>Điền đầy đủ thông tin để thêm sản phẩm vào hệ thống</p>
    </div>

    <div class="st-form-body">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i> <strong>Vui lòng kiểm tra lại:</strong>
          <ul style="margin:8px 0 0 16px;">
            <?php foreach ($errors as $error): ?>
              <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="/Product/save" enctype="multipart/form-data" class="st-validate">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

          <!-- Name -->
          <div style="grid-column:1/-1;">
            <label class="form-label" for="name">
              <i class="bi bi-tag" style="color:var(--primary)"></i> Tên sản phẩm <span style="color:var(--danger)">*</span>
            </label>
            <input type="text" id="name" name="name" class="form-control"
                   placeholder="Nhập tên sản phẩm..." required>
          </div>

          <!-- Price -->
          <div>
            <label class="form-label" for="price">
              <i class="bi bi-currency-dollar" style="color:var(--primary)"></i> Giá (₫) <span style="color:var(--danger)">*</span>
            </label>
            <input type="number" id="price" name="price" class="form-control"
                   placeholder="0" min="0" step="1000" required>
          </div>

          <!-- Category -->
          <div>
            <label class="form-label" for="category_id">
              <i class="bi bi-grid" style="color:var(--primary)"></i> Danh mục <span style="color:var(--danger)">*</span>
            </label>
            <select id="category_id" name="category_id" class="form-select" required>
              <option value="">Chọn danh mục</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category->id; ?>">
                  <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Description -->
          <div style="grid-column:1/-1;">
            <label class="form-label" for="description">
              <i class="bi bi-text-paragraph" style="color:var(--primary)"></i> Mô tả <span style="color:var(--danger)">*</span>
            </label>
            <textarea id="description" name="description" class="form-control"
                      placeholder="Nhập mô tả sản phẩm..." required></textarea>
          </div>

          <!-- Image upload -->
          <div style="grid-column:1/-1;">
            <label class="form-label">
              <i class="bi bi-image" style="color:var(--primary)"></i> Hình ảnh
            </label>
            <div class="st-img-preview-wrap">
              <input type="file" id="imageInput" name="image" accept="image/*">
              
            </div>
          </div>

        </div><!-- /grid -->

        <div class="st-form-actions" style="margin-top:24px;">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle"></i> Thêm sản phẩm
          </button>
          <a href="/Product/list" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Huỷ
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>