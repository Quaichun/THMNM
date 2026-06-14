<?php include 'app/views/shares/header.php'; ?>

<div class="st-page-head fade-up">
  <div class="st-breadcrumb">
    <a href="/Product/">Trang chủ</a>
    <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
    <a href="/Category/list">Danh mục</a>
    <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
    <span>Thêm danh mục</span>
  </div>
</div>

<div class="st-form-page fade-up">
  <div class="st-form-card">

    <div class="st-form-header" style="background:linear-gradient(135deg,#059669,#10b981);">
      <h1><i class="bi bi-folder-plus"></i> Thêm danh mục mới</h1>
      <p>Tạo danh mục để phân loại sản phẩm dễ dàng hơn</p>
    </div>

    <div class="st-form-body">
      <form id="addCategoryForm" class="st-validate">

        <div style="display:flex;flex-direction:column;gap:20px;">

          <div>
            <label class="form-label" for="name">
              <i class="bi bi-tag" style="color:var(--primary)"></i> Tên danh mục <span style="color:var(--danger)">*</span>
            </label>
            <input type="text" id="name" name="name" class="form-control"
                   placeholder="VD: Điện thoại, Laptop, Phụ kiện..." required>
          </div>

          <div>
            <label class="form-label" for="description">
              <i class="bi bi-text-paragraph" style="color:var(--primary)"></i> Mô tả
            </label>
            <textarea id="description" name="description" class="form-control"
                      placeholder="Mô tả ngắn về danh mục này..."></textarea>
          </div>

        </div>

        <div class="st-form-actions" style="margin-top:24px;">
          <button type="submit" class="btn btn-success btn-lg" id="btnSubmit">
            <i class="bi bi-plus-circle"></i> Thêm danh mục
          </button>
          <a href="/Category/list" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Huỷ
          </a>
        </div>

      </form>

      <script>
      document.getElementById('addCategoryForm').addEventListener('submit', async (e) => {
          e.preventDefault();
          const btn = document.getElementById('btnSubmit');
          const formData = new FormData(e.target);
          const data = Object.fromEntries(formData.entries());

          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang lưu...';

          try {
              const res = await apiFetch('/api/category/store', {
                  method: 'POST',
                  body: JSON.stringify(data)
              });
              const result = await res.json();
              if (result.success) {
                  showToast('Đã thêm danh mục!', '✅');
                  setTimeout(() => window.location.href = '/Category/list', 1500);
              } else {
                  alert(result.message || 'Lỗi khi thêm danh mục');
              }
          } catch (err) {
              alert('Lỗi kết nối');
          } finally {
              btn.disabled = false;
              btn.innerHTML = '<i class="bi bi-plus-circle"></i> Thêm danh mục';
          }
      });
      </script>

    </div>
  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>