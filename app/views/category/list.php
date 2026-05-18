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
      <tr>
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
            <a href="/Category/delete/<?php echo $category->id; ?>" class="btn btn-danger btn-sm btn-delete-confirm">
              <i class="bi bi-trash"></i> Xoá
            </a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

</div>

<?php include 'app/views/shares/footer.php'; ?>