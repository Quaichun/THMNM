<?php
require_once 'app/helpers/SessionHelper.php';
$flash = SessionHelper::getFlash('success');
$verifyLink = SessionHelper::getFlash('verify_link');
?>
<?php include 'app/views/shares/header.php'; ?>

<div class="st-auth-page">
  <div class="st-auth-card fade-up">

    <div class="st-auth-header">
      <div class="st-auth-logo">🛍️</div>
      <h1>Đăng nhập</h1>
      <p>Chào mừng trở lại ShopTech!</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!empty($verifyLink)): ?>
      <div class="alert alert-info">
        <div class="mb-2">Bạn có thể xác thực email bằng một trong hai cách:</div>
        <a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8'); ?>">
          <i class="bi bi-patch-check"></i> Xác thực ngay
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8'); ?>')">
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

    <form method="POST" action="/Account/login" id="loginForm">

      <div class="st-input-group">
        <label class="form-label">Tên đăng nhập</label>
        <div class="st-input-wrap">
          <i class="bi bi-person st-input-icon"></i>
          <input type="text" name="username" class="form-control st-input-icon-pad"
                 placeholder="Nhập tên đăng nhập"
                 value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                 autocomplete="username" required>
        </div>
      </div>

      <div class="st-input-group">
        <label class="form-label">Mật khẩu</label>
        <div class="st-input-wrap">
          <i class="bi bi-lock st-input-icon"></i>
          <input type="password" name="password" id="passwordInput"
                 class="form-control st-input-icon-pad"
                 placeholder="Nhập mật khẩu"
                 autocomplete="current-password" required>
          <button type="button" class="st-pw-toggle" onclick="togglePw('passwordInput', this)">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="form-check m-0">
          <input class="form-check-input" type="checkbox" value="1" id="rememberMe" name="remember_me">
          <label class="form-check-label" for="rememberMe">Ghi nhớ đăng nhập</label>
        </div>
        <a href="/Account/forgotPassword" style="font-size:.9rem">Quên mật khẩu?</a>
      </div>

      <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">
        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
      </button>

    </form>

    <div class="st-auth-footer">
      Chưa có tài khoản?
      <a href="/Account/register">Đăng ký ngay</a>
    </div>

  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
