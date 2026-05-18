<?php include 'app/views/shares/header.php'; ?>

<div class="st-page-head fade-up">
  <div class="st-breadcrumb">
    <a href="/Product/">Trang chủ</a>
    <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
    <a href="/Product/list">Sản phẩm</a>
    <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
    <span>Sửa sản phẩm</span>
  </div>
</div>

<div class="st-form-page fade-up">
  <div class="st-form-card">

    <div class="st-form-header" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
      <h1><i class="bi bi-pencil-square"></i> Sửa sản phẩm</h1>
      <p>Cập nhật thông tin sản phẩm trong hệ thống</p>
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

      <form method="POST" action="/Product/update" enctype="multipart/form-data" class="st-validate">
        <input type="hidden" name="id" value="<?php echo $product->id; ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

          <!-- Name -->
          <div style="grid-column:1/-1;">
            <label class="form-label" for="name">
              <i class="bi bi-tag" style="color:var(--primary)"></i> Tên sản phẩm <span style="color:var(--danger)">*</span>
            </label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <!-- Price -->
          <div>
            <label class="form-label" for="price">
              <i class="bi bi-currency-dollar" style="color:var(--primary)"></i> Giá (₫) <span style="color:var(--danger)">*</span>
            </label>
            <input type="number" id="price" name="price" class="form-control"
                   value="<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?>"
                   min="0" step="1000" required>
          </div>

          <!-- Category -->
          <div>
            <label class="form-label" for="category_id">
              <i class="bi bi-grid" style="color:var(--primary)"></i> Danh mục
            </label>
            <select id="category_id" name="category_id" class="form-select">
              <option value="">Chọn danh mục</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category->id; ?>"
                  <?php echo $category->id == $product->category_id ? 'selected' : ''; ?>>
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
            <textarea id="description" name="description" class="form-control" required
              ><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <!-- Image -->
          <div style="grid-column:1/-1;">
            <label class="form-label">
              <i class="bi bi-image" style="color:var(--primary)"></i> Hình ảnh
            </label>
            <input type="hidden" name="existing_image" value="<?php echo $product->image; ?>">

            <div class="st-img-preview-wrap <?php echo $product->image ? 'has-img' : ''; ?>">
              <input type="file" id="imageInput" name="image" accept="image/*">

              <?php if ($product->image): ?>
                <img class="st-existing-preview"
                     src="/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>"
                     alt="Ảnh hiện tại"
                     style="max-height:160px;border-radius:8px;display:block;margin:auto;">
              <?php else: ?>
                <div class="st-upload-placeholder">
                  
                  <p style="font-size:.78rem;margin-top:4px;">JPG, PNG, GIF – tối đa 10MB</p>
                </div>
              <?php endif; ?>

              <div class="st-img-overlay">
                <span style="color:var(--primary);font-weight:700;font-size:.9rem;">🔄 Thay đổi ảnh</span>
              </div>
            </div>
          </div>

        </div><!-- /grid -->

        <div class="st-form-actions" style="margin-top:24px;">
          <button type="submit" class="btn btn-warning btn-lg">
            <i class="bi bi-save"></i> Lưu thay đổi
          </button>
          <a href="/Product/list" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Huỷ
          </a>
          <a href="/Product/delete/<?php echo $product->id; ?>" class="btn btn-danger btn-sm btn-delete-confirm" style="margin-left:auto;">
            <i class="bi bi-trash"></i> Xoá sản phẩm
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>