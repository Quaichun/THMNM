<?php include 'app/views/shares/header.php'; ?>

<div class="st-auth-page">
  <div class="st-auth-card fade-up">

    <div class="st-auth-header">
      <div class="st-auth-logo">🛍️</div>
      <h1>Tạo tài khoản</h1>
      <p>Tham gia ShopTech để mua sắm dễ dàng hơn</p>
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

    <form method="POST" action="/Account/register" id="registerForm">

      <div class="st-input-group">
        <label class="form-label">Họ và tên *</label>
        <div class="st-input-wrap">
          <i class="bi bi-person-badge st-input-icon"></i>
          <input type="text" name="fullname" class="form-control st-input-icon-pad"
                 placeholder="Nguyễn Văn A"
                 value="<?php echo htmlspecialchars($_POST['fullname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                 required>
        </div>
      </div>

      <div class="st-input-group">
        <label class="form-label">Tên đăng nhập *</label>
        <div class="st-input-wrap">
          <i class="bi bi-at st-input-icon"></i>
          <input type="text" name="username" class="form-control st-input-icon-pad"
                 placeholder="Chỉ gồm chữ, số, dấu _"
                 value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                 autocomplete="username" required>
        </div>
      </div>

      <div class="st-input-group">
        <label class="form-label">Email *</label>
        <div class="st-input-wrap">
          <i class="bi bi-envelope st-input-icon"></i>
          <input type="email" name="email" class="form-control st-input-icon-pad"
                 placeholder="email@domain.com"
                 value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                 autocomplete="email" required>
        </div>
      </div>

      <div class="st-input-group">
        <label class="form-label">Mật khẩu *</label>
        <div class="st-input-wrap">
          <i class="bi bi-lock st-input-icon"></i>
          <input type="password" name="password" id="pw1"
                 class="form-control st-input-icon-pad"
                 placeholder="Ít nhất 6 ký tự"
                 autocomplete="new-password" required>
          <button type="button" class="st-pw-toggle" onclick="togglePw('pw1', this)">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <!-- Strength bar -->
        <div class="st-pw-strength-wrap">
          <div class="st-pw-strength-bar" id="pwStrengthBar"></div>
        </div>
        <div class="st-pw-strength-label" id="pwStrengthLabel"></div>
      </div>

      <div class="st-input-group">
        <label class="form-label">Xác nhận mật khẩu *</label>
        <div class="st-input-wrap">
          <i class="bi bi-lock-fill st-input-icon"></i>
          <input type="password" name="confirm_password" id="pw2"
                 class="form-control st-input-icon-pad"
                 placeholder="Nhập lại mật khẩu"
                 autocomplete="new-password" required>
          <button type="button" class="st-pw-toggle" onclick="togglePw('pw2', this)">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <div class="st-pw-match" id="pwMatch"></div>
      </div>

      <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">
        <i class="bi bi-person-check"></i> Tạo tài khoản
      </button>

    </form>

    <div class="st-auth-footer">
      Đã có tài khoản?
      <a href="/Account/login">Đăng nhập</a>
    </div>

  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
