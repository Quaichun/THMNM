<?php require_once 'app/helpers/SessionHelper.php'; $flash=SessionHelper::getFlash('success'); ?>
<?php include 'app/views/shares/header.php'; ?>

<div class="st-auth-page">
  <div class="st-auth-card fade-up">
    <div class="st-auth-header">
      <div class="st-auth-logo">🔐</div>
      <h1>Quên mật khẩu</h1>
      <p>Nhập email để nhận link đặt lại mật khẩu</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!empty($resetLink)): ?>
      <div class="alert alert-info">
        <div class="mb-2">Đã tạo link đặt lại mật khẩu.</div>
        <a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'); ?>">
          <i class="bi bi-box-arrow-up-right"></i> Mở trang đặt lại
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'); ?>')">
          <i class="bi bi-clipboard"></i> Sao chép link
        </button>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul style="margin:0;padding-left:18px">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="/Account/forgotPassword">
      <div class="st-input-group">
        <label class="form-label">Email</label>
        <div class="st-input-wrap">
          <i class="bi bi-envelope st-input-icon"></i>
          <input type="email" name="email" class="form-control st-input-icon-pad" placeholder="email@domain.com" required>
        </div>
      </div>
      <button class="btn btn-primary btn-lg w-100 mt-2" type="submit">
        <i class="bi bi-send"></i> Gửi link đặt lại
      </button>
    </form>
  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
