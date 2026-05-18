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
      <form method="POST" action="/Category/update" class="st-validate">
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
          <button type="submit" class="btn btn-warning btn-lg">
            <i class="bi bi-save"></i> Lưu thay đổi
          </button>
          <a href="/Category/list" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Huỷ
          </a>
          <a href="/Category/delete/<?php echo $category->id; ?>" class="btn btn-danger btn-sm btn-delete-confirm" style="margin-left:auto;">
            <i class="bi bi-trash"></i> Xoá
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>