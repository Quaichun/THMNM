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

      <form id="editProductForm" enctype="multipart/form-data" class="st-validate">
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
            
            <div class="st-img-preview-wrap <?php echo $product->image ? 'has-img' : ''; ?>">
              <input type="file" id="imageInput" name="image" accept="image/*">

              <?php if ($product->image): ?>
                <div style="text-align:center; margin-top:10px;">
                  <img class="st-existing-preview"
                       src="/<?php echo htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8'); ?>"
                       alt="Ảnh hiện tại"
                       style="max-height:160px;border-radius:8px;display:inline-block;">
                  <p style="font-size:0.8rem; color: #666;">Ảnh hiện tại</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Technical Specs -->
          <div style="grid-column:1/-1; margin-top: 10px;">
            <label class="form-label">
              <i class="bi bi-cpu" style="color:var(--primary)"></i> Thông số kỹ thuật
            </label>
            <div id="specsContainer">
              <?php if (!empty($specs)): ?>
                <?php foreach ($specs as $index => $spec): ?>
                  <div class="spec-row" style="display:flex; gap:10px; margin-bottom:10px;">
                    <input type="text" name="specs[<?php echo $index; ?>][name]" 
                           value="<?php echo htmlspecialchars($spec->spec_name, ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                    <input type="text" name="specs[<?php echo $index; ?>][value]" 
                           value="<?php echo htmlspecialchars($spec->spec_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                    <button type="button" class="btn btn-outline-danger remove-spec" onclick="this.parentElement.remove()"><i class="bi bi-trash"></i></button>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="spec-row" style="display:flex; gap:10px; margin-bottom:10px;">
                  <input type="text" name="specs[0][name]" placeholder="Tên thông số (VD: RAM)" class="form-control">
                  <input type="text" name="specs[0][value]" placeholder="Giá trị (VD: 8GB)" class="form-control">
                  <button type="button" class="btn btn-outline-danger remove-spec" onclick="this.parentElement.remove()" style="display:none;"><i class="bi bi-trash"></i></button>
                </div>
              <?php endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-success" id="addSpecBtn"><i class="bi bi-plus"></i> Thêm thông số</button>
          </div>

        </div><!-- /grid -->

        <div class="st-form-actions" style="margin-top:24px;">
          <button type="submit" class="btn btn-warning btn-lg" id="btnUpdate">
            <i class="bi bi-save"></i> Lưu thay đổi
          </button>
          <a href="/Product/list" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Huỷ
          </a>
          <button type="button" id="btnDelete" class="btn btn-danger btn-sm" style="margin-left:auto;">
            <i class="bi bi-trash"></i> Xoá sản phẩm
          </button>
        </div>

      </form>

      <script>
        // Spec logic
        let specIndex = <?php echo !empty($specs) ? count($specs) : 1; ?>;
        document.getElementById('addSpecBtn').onclick = function() {
          const container = document.getElementById('specsContainer');
          const row = document.createElement('div');
          row.className = 'spec-row'; row.style.display = 'flex'; row.style.gap = '10px'; row.style.marginBottom = '10px';
          row.innerHTML = `<input type="text" name="specs[${specIndex}][name]" placeholder="Tên" class="form-control"><input type="text" name="specs[${specIndex}][value]" placeholder="Giá trị" class="form-control"><button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()"><i class="bi bi-trash"></i></button>`;
          container.appendChild(row);
          specIndex++;
        };

        const productId = <?php echo (int)$product->id; ?>;

        // Submit via API
        document.getElementById('editProductForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnUpdate');
            const formData = new FormData(e.target);
            
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang lưu...';

            try {
                const res = await apiFetch(`/api/product/update/${productId}`, {
                    method: 'POST', // Dùng POST để PHP nhận multipart/form-data dễ dàng hơn
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Đã cập nhật sản phẩm!', '✅');
                    setTimeout(() => window.location.href = '/Product/list', 1500);
                } else {
                    alert(result.message || 'Lỗi khi cập nhật');
                }
            } catch (err) {
                alert('Lỗi kết nối');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-save"></i> Lưu thay đổi';
            }
        });

        // Delete via API
        document.getElementById('btnDelete').addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
            try {
                const res = await apiFetch(`/api/product/destroy/${productId}`, { method: 'DELETE' });
                const result = await res.json();
                if (result.success) {
                    showToast('Đã xóa sản phẩm!', '🗑️');
                    setTimeout(() => window.location.href = '/Product/list', 1200);
                } else alert(result.message);
            } catch (err) { alert('Lỗi kết nối'); }
        });
      </script>

    </div>
  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>