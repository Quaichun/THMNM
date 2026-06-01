<?php include 'app/views/shares/header.php'; ?>

<div class="st-auth-page">
  <div class="st-auth-card fade-up">
    <div class="st-auth-header">
      <div class="st-auth-logo">🔁</div>
      <h1>Đặt lại mật khẩu</h1>
      <p>Tạo mật khẩu mới cho tài khoản của bạn</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul style="margin:0;padding-left:18px">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($user): ?>
      <form method="POST" action="/Account/resetPassword">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="st-input-group">
          <label class="form-label">Mật khẩu mới</label>
          <div class="st-input-wrap">
            <i class="bi bi-lock st-input-icon"></i>
            <input type="password" name="password" class="form-control st-input-icon-pad" required>
          </div>
        </div>

        <div class="st-input-group">
          <label class="form-label">Xác nhận mật khẩu</label>
          <div class="st-input-wrap">
            <i class="bi bi-lock-fill st-input-icon"></i>
            <input type="password" name="confirm_password" class="form-control st-input-icon-pad" required>
          </div>
        </div>

        <button class="btn btn-primary btn-lg w-100 mt-2" type="submit">
          <i class="bi bi-check2-circle"></i> Cập nhật mật khẩu
        </button>
      </form>
    <?php else: ?>
      <div class="alert alert-warning">
        Link không hợp lệ hoặc đã hết hạn. Vui lòng tạo link mới ở trang quên mật khẩu.
      </div>
      <a class="btn btn-outline-primary w-100" href="/Account/forgotPassword">Tạo link mới</a>
    <?php endif; ?>
  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
