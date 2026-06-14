<?php include 'app/views/shares/header.php'; ?>

<div class="st-page-head fade-up">
  <div class="st-breadcrumb">
    <a href="/Product/">Trang chủ</a>
    <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
    <span>Quản lý danh mục</span>
  </div>
</div>

<div class="st-table-card fade-up">

  <div class="st-table-header">
    <h1><i class="bi bi-grid-3x3-gap"></i> Quản lý danh mục</h1>
    <a href="/Category/add" class="btn btn-success">
      <i class="bi bi-plus-circle"></i> Thêm danh mục
    </a>
  </div>

  <?php if (empty($categories)): ?>
    <div class="st-empty">
      <div class="st-empty-icon">🗂️</div>
      <h3>Chưa có danh mục nào</h3>
      <p>Tạo danh mục đầu tiên để phân loại sản phẩm.</p>
      <a href="/Category/add" class="btn btn-primary mt-3">+ Thêm danh mục</a>
    </div>
  <?php else: ?>
  <table class="st-table">
    <thead>
      <tr>
        <th style="width:60px;">STT</th>
        <th>Tên danh mục</th>
        <th>Mô tả</th>
        <th style="width:160px;text-align:center;">Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $i => $category): ?>
      <tr id="cat-row-<?php echo $category->id; ?>">
        <td style="color:var(--text-muted);font-weight:700;"><?php echo $i + 1; ?></td>
        <td>
          <span style="font-weight:700;color:var(--text-dark);">
            <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
          </span>
        </td>
        <td style="color:var(--text-muted);">
          <?php echo htmlspecialchars($category->description, ENT_QUOTES, 'UTF-8') ?: '—'; ?>
        </td>
        <td style="text-align:center;">
          <div style="display:flex;gap:8px;justify-content:center;">
            <a href="/Category/edit/<?php echo $category->id; ?>" class="btn btn-warning btn-sm">
              <i class="bi bi-pencil"></i> Sửa
            </a>
            <button type="button" class="btn btn-danger btn-sm btn-delete-cat" data-id="<?php echo $category->id; ?>">
              <i class="bi bi-trash"></i> Xoá
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

</div>

<script>
document.querySelectorAll('.btn-delete-cat').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        if (!confirm('Bạn có chắc muốn xóa danh mục này?')) return;
        
        try {
            const res = await apiFetch(`/api/category/destroy/${id}`, { method: 'DELETE' });
            const result = await res.json();
            if (result.success) {
                showToast('Đã xóa danh mục!', '🗑️');
                document.getElementById(`cat-row-${id}`).remove();
            } else {
                alert(result.message || 'Lỗi khi xóa');
            }
        } catch (err) {
            alert('Lỗi kết nối');
        }
    });
});
</script>


<?php include 'app/views/shares/footer.php'; ?>