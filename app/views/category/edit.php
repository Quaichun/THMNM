<?php include 'app/views/shares/header.php'; ?>

<div class="st-page-head fade-up">
  <div class="st-breadcrumb">
    <a href="/Product/">Trang chủ</a>
    <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
    <a href="/Category/list">Danh mục</a>
    <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
    <span>Sửa danh mục</span>
  </div>
</div>

<div class="st-form-page fade-up">
  <div class="st-form-card">

    <div class="st-form-header" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
      <h1><i class="bi bi-pencil-square"></i> Sửa danh mục</h1>
      <p>Cập nhật thông tin danh mục</p>
    </div>

    <div class="st-form-body">
      <form id="editCategoryForm" class="st-validate">
        <input type="hidden" name="id" value="<?php echo $category->id; ?>">

        <div style="display:flex;flex-direction:column;gap:20px;">

          <div>
            <label class="form-label" for="name">
              <i class="bi bi-tag" style="color:var(--primary)"></i> Tên danh mục <span style="color:var(--danger)">*</span>
            </label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div>
            <label class="form-label" for="description">
              <i class="bi bi-text-paragraph" style="color:var(--primary)"></i> Mô tả
            </label>
            <textarea id="description" name="description" class="form-control"
              ><?php echo htmlspecialchars($category->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

        </div>

        <div class="st-form-actions" style="margin-top:24px;">
          <button type="submit" class="btn btn-warning btn-lg" id="btnUpdate">
            <i class="bi bi-save"></i> Lưu thay đổi
          </button>
          <a href="/Category/list" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Huỷ
          </a>
          <button type="button" id="btnDelete" class="btn btn-danger btn-sm" style="margin-left:auto;">
            <i class="bi bi-trash"></i> Xoá
          </button>
        </div>

      </form>

      <script>
      const categoryId = <?php echo (int)$category->id; ?>;
      
      document.getElementById('editCategoryForm').addEventListener('submit', async (e) => {
          e.preventDefault();
          const btn = document.getElementById('btnUpdate');
          const formData = new FormData(e.target);
          const data = Object.fromEntries(formData.entries());

          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang lưu...';

          try {
              const res = await apiFetch(`/api/category/update/${categoryId}`, {
                  method: 'POST', // Hoặc 'PUT' bằng JSON
                  body: JSON.stringify(data)
              });
              const result = await res.json();
              if (result.success) {
                  showToast('Đã cập nhật danh mục!', '✅');
                  setTimeout(() => window.location.href = '/Category/list', 1200);
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

      document.getElementById('btnDelete').addEventListener('click', async () => {
          if (!confirm('Bạn có chắc muốn xóa danh mục này?')) return;
          try {
              const res = await apiFetch(`/api/category/destroy/${categoryId}`, { method: 'DELETE' });
              const result = await res.json();
              if (result.success) {
                  showToast('Đã xóa danh mục!', '🗑️');
                  setTimeout(() => window.location.href = '/Category/list', 1200);
              } else alert(result.message);
          } catch (err) { alert('Lỗi kết nối'); }
      });
      </script>

    </div>
  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>