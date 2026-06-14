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
