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

      <button type="submit" class="btn btn-primary btn-lg w-100 mt-2" id="btnRegister">
        <i class="bi bi-person-check"></i> Tạo tài khoản
      </button>

    </form>

    <script>
    // --- Password Strength & Match ---
    const pw1 = document.getElementById('pw1');
    const pw2 = document.getElementById('pw2');
    const bar = document.getElementById('pwStrengthBar');
    const label = document.getElementById('pwStrengthLabel');
    const match = document.getElementById('pwMatch');

    pw1?.addEventListener('input', () => {
        const val = pw1.value;
        let score = 0;
        if (val.length >= 6) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const maps = [
            { w: '20%', c: '#ef4444', t: 'Rất yếu' },
            { w: '40%', c: '#f59e0b', t: 'Yếu' },
            { w: '60%', c: '#3b82f6', t: 'Trung bình' },
            { w: '80%', c: '#10b981', t: 'Mạnh' },
            { w: '100%', c: '#059669', t: 'Rất mạnh' }
        ];

        if (!val) {
            bar.style.width = '0';
            label.textContent = '';
        } else {
            const m = maps[score];
            bar.style.width = m.w;
            bar.style.background = m.c;
            label.textContent = m.t;
            label.style.color = m.c;
        }
    });

    const checkMatch = () => {
        if (!pw2.value) { match.textContent = ''; return; }
        if (pw1.value === pw2.value) {
            match.textContent = '✅ Mật khẩu xác nhận khớp';
            match.style.color = 'var(--success)';
        } else {
            match.textContent = '❌ Mật khẩu xác nhận không khớp';
            match.style.color = 'var(--danger)';
        }
    };
    pw1?.addEventListener('input', checkMatch);
    pw2?.addEventListener('input', checkMatch);

    // --- Form Submit ---
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (pw1.value !== pw2.value) {
            alert('Mật khẩu xác nhận không khớp');
            return;
        }

        const btn = document.getElementById('btnRegister');
        const formData = new FormData(e.target);
        
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';

        try {
            // Use the traditional POST for registration to ensure email is sent
            // (The API version doesn't handle MailHelper yet)
            const res = await fetch('/Account/register', {
                method: 'POST',
                body: formData
            });

            // If the server redirects, it means success (AccountController redirects to login)
            if (res.redirected) {
                window.location.href = res.url;
            } else {
                // If not redirected, there might be errors (displayed in the PHP view)
                // We reload to show errors
                e.target.submit(); 
            }
        } catch (err) {
            console.error(err);
            alert('Có lỗi xảy ra, vui lòng thử lại');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-check"></i> Tạo tài khoản';
        }
    });
    </script>


    <div class="st-auth-footer">
      Đã có tài khoản?
      <a href="/Account/login">Đăng nhập</a>
    </div>

  </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
