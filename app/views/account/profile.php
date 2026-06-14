<?php
require_once 'app/helpers/SessionHelper.php';
SessionHelper::requireLogin();
$flash      = SessionHelper::getFlash('success');
$flashError = SessionHelper::getFlash('error');
$verifyLink = SessionHelper::getFlash('verify_link');
$avatarUrl  = !empty($user->avatar)
    ? '/' . htmlspecialchars($user->avatar, ENT_QUOTES, 'UTF-8')
    : null;
?>
<?php include 'app/views/shares/header.php'; ?>

<div class="st-page">
<div class="st-container">

  <div class="st-page-head fade-up">
    <div>
      <h1>👤 Hồ sơ cá nhân</h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> › <span>Hồ sơ</span>
      </div>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-success fade-up">
      ✅ <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($verifyLink)): ?>
    <div class="alert alert-info fade-up">
      <div class="mb-2">Email mới chưa xác thực. Bạn có thể xác thực ngay:</div>
      <a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8'); ?>">
        <i class="bi bi-patch-check"></i> Xác thực email
      </a>
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8'); ?>')">
        <i class="bi bi-clipboard"></i> Sao chép link
      </button>
    </div>
  <?php endif; ?>

  <?php if ($flashError): ?>
    <div class="alert alert-danger fade-up">
      ❌ <?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger fade-up">
      <ul style="margin:0;padding-left:18px">
        <?php foreach ($errors as $e): ?>
          <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="st-profile-layout">

    <!-- ─── Sidebar ─── -->
    <div class="st-profile-sidebar fade-up">

      <!-- Avatar box -->
      <div class="st-avatar-box">

        <!-- Avatar preview -->
        <div class="st-avatar-upload-wrap" id="avatarWrap">

          <?php if ($avatarUrl): ?>
            <img src="<?php echo $avatarUrl; ?>"
                 alt="Avatar" class="st-avatar-img" id="avatarPreview">
          <?php else: ?>
            <div class="st-avatar-circle" id="avatarInitial">
              <?php echo strtoupper(mb_substr($user->fullname, 0, 1)); ?>
            </div>
          <?php endif; ?>

          <!-- Overlay khi hover -->
          <label for="avatarFileInput" class="st-avatar-overlay" title="Đổi ảnh đại diện">
            <i class="bi bi-camera-fill"></i>
            <span>Đổi ảnh</span>
          </label>

        </div>

        <!-- Hidden form upload -->
        <form method="POST" action="/Account/updateAvatar"
              enctype="multipart/form-data" id="avatarForm">
          <input type="file" id="avatarFileInput" name="avatar"
                 accept="image/jpeg,image/png,image/gif,image/webp"
                 style="display:none">
        </form>

        <div class="st-avatar-info">
          <strong><?php echo htmlspecialchars($user->fullname, ENT_QUOTES, 'UTF-8'); ?></strong>
          <span>@<?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="st-role-badge <?php echo $user->role === 'admin' ? 'admin' : 'user'; ?>">
            <?php echo $user->role === 'admin' ? '⚙️ Admin' : '👤 Người dùng'; ?>
          </span>
        </div>

        <!-- Upload status -->
        <div class="st-avatar-upload-status" id="avatarStatus"></div>

      </div>

      <!-- Profile nav -->
      <nav class="st-profile-nav">
        <a href="?tab=info"
           class="st-profile-nav-item <?php echo $tab === 'info' ? 'active' : ''; ?>">
          <i class="bi bi-person-fill"></i> Thông tin cá nhân
        </a>
        <a href="?tab=password"
           class="st-profile-nav-item <?php echo $tab === 'password' ? 'active' : ''; ?>">
          <i class="bi bi-shield-lock-fill"></i> Đổi mật khẩu
        </a>
        <a href="/Product/myOrders" class="st-profile-nav-item">
          <i class="bi bi-receipt"></i> Đơn hàng của tôi
        </a>
        <a href="/Product/cart" class="st-profile-nav-item">
          <i class="bi bi-cart3"></i> Giỏ hàng
        </a>
        <div class="st-profile-nav-divider"></div>
        <a href="/Account/logout"
           class="st-profile-nav-item logout"
           onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
          <i class="bi bi-box-arrow-right"></i> Đăng xuất
        </a>
      </nav>

    </div>

    <!-- ─── Main content ─── -->
    <div class="st-profile-content fade-up">

      <?php if ($tab === 'info'): ?>
      <!-- ── Tab thông tin ── -->
      <div class="st-form-card">
        <div class="st-form-header">
          <h1>📋 Thông tin cá nhân</h1>
          <p>Cập nhật họ tên, tên đăng nhập và email của bạn</p>
        </div>
        <div class="st-form-body">
          <form id="updateProfileForm">

            <div class="mb-4">
              <label class="form-label">Họ và tên *</label>
              <div class="st-input-wrap">
                <i class="bi bi-person-badge st-input-icon"></i>
                <input type="text" name="fullname"
                       class="form-control st-input-icon-pad"
                       value="<?php echo htmlspecialchars($user->fullname ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Tên đăng nhập *</label>
              <div class="st-input-wrap">
                <i class="bi bi-at st-input-icon"></i>
                <input type="text" name="username"
                       class="form-control st-input-icon-pad"
                       value="<?php echo htmlspecialchars($user->username ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Email *</label>
              <div class="st-input-wrap">
                <i class="bi bi-envelope st-input-icon"></i>
                <input type="email" name="email"
                       class="form-control st-input-icon-pad"
                       value="<?php echo htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                       required>
              </div>
              <small style="color:var(--text-muted)">
                <?php if (!empty($user->email_verified_at)): ?>
                  <span class="text-success">✅ Email đã xác thực (<?php echo date('d/m/Y', strtotime($user->email_verified_at)); ?>)</span>
                <?php else: ?>
                  <span class="text-warning">⚠️ Email chưa xác thực</span>
                  <?php 
                  $vLink = SessionHelper::getFlash('verify_link');
                  if ($vLink): ?>
                    <div class="mt-2 p-2 border rounded bg-light fade-up">
                      <p class="mb-1 text-primary" style="font-size:0.8rem"><strong>Chế độ Test:</strong> Xác thực email mới tại đây:</p>
                      <a href="<?php echo $vLink; ?>" class="btn btn-sm btn-outline-primary py-0">Nhấn để xác thực ngay</a>
                    </div>
                  <?php endif; ?>
                <?php endif; ?>
              </small>
            </div>

            <div class="mb-4">
              <label class="form-label">Vai trò</label>
              <input type="text" class="form-control"
                     value="<?php echo ($user->role ?? 'user') === 'admin' ? 'Quản trị viên' : 'Người dùng'; ?>"
                     disabled style="opacity:.6;cursor:not-allowed">
            </div>

            <div class="st-form-actions">
              <button type="submit" class="btn btn-primary" id="btnUpdateProfile">
                <i class="bi bi-check-lg"></i> Lưu thay đổi
              </button>
            </div>

          </form>
        </div>
      </div>

      <script>
      document.getElementById('updateProfileForm')?.addEventListener('submit', async (e) => {
          e.preventDefault();
          const btn = document.getElementById('btnUpdateProfile');
          const formData = new FormData(e.target);
          const data = Object.fromEntries(formData.entries());

          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang tải...';

          try {
              const res = await apiFetch('/api/account/updateProfile', {
                  method: 'POST',
                  body: JSON.stringify(data)
              });
              const result = await res.json();
              if (result.success) {
                  showToast('Đã cập nhật hồ sơ!', '👤');
                  setTimeout(() => location.reload(), 1200);
              } else {
                  let msg = result.message || 'Lỗi cập nhật';
                  if (result.errors) msg = Object.values(result.errors).join('\n');
                  alert(msg);
              }
          } catch (e) { alert('Lỗi kết nối'); } finally {
              btn.disabled = false;
              btn.innerHTML = '<i class="bi bi-check-lg"></i> Lưu thay đổi';
          }
      });
      </script>

      <!-- Thẻ ảnh đại diện riêng -->
      <div class="st-form-card" style="margin-top:20px">
        <div class="st-form-header">
          <h1>🖼️ Ảnh đại diện</h1>
          <p>JPG, PNG, GIF hoặc WEBP — tối đa 3MB</p>
        </div>
        <div class="st-form-body">

          <div class="st-avatar-detail-wrap">

            <!-- Preview lớn -->
            <div class="st-avatar-detail-preview" id="avatarDetailPreview">
              <?php if ($avatarUrl): ?>
                <img src="<?php echo $avatarUrl; ?>" id="avatarDetailImg" alt="Avatar">
              <?php else: ?>
                <div class="st-avatar-detail-initial" id="avatarDetailInitial">
                  <?php echo strtoupper(mb_substr($user->fullname ?? 'U', 0, 1)); ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Upload area -->
            <div class="st-avatar-detail-right">
              <div class="st-upload-box" id="avatarDropZone">
                <label class="st-file-label" for="avatarFileInput2">
                  <span class="st-file-icon">📷</span>
                  <span class="st-file-text">
                    <strong>Chọn ảnh</strong> hoặc kéo thả vào đây<br>
                    <small>JPG, PNG, GIF, WEBP — tối đa 3MB</small>
                  </span>
                </label>
                <div class="st-file-input-row">
                  <input type="file" id="avatarFileInput2" name="avatar2"
                         accept="image/jpeg,image/png,image/gif,image/webp"
                         class="form-control">
                </div>
              </div>

              <!-- Preview mới trước khi lưu -->
              <div class="st-img-preview" id="avatarNewPreview">
                <img src="" id="avatarNewImg" alt="Preview">
                <div class="st-img-preview-info">
                  <span id="avatarNewName">—</span>
                  <small id="avatarNewSize">—</small>
                </div>
                <button type="button" class="st-img-remove" id="avatarRemoveBtn">
                  <i class="bi bi-x-circle-fill"></i>
                </button>
              </div>

              <form method="POST" action="/Account/updateAvatar"
                    enctype="multipart/form-data" id="avatarFormDetail">
                <input type="file" name="avatar" id="avatarFileHidden" style="display:none">
                <button type="submit" class="btn btn-primary mt-3" id="avatarSaveBtn"
                        style="display:none">
                  <i class="bi bi-cloud-upload"></i> Lưu ảnh đại diện
                </button>
              </form>

              <?php if ($avatarUrl): ?>
                <p style="font-size:.8rem;color:var(--text-muted);margin-top:10px">
                  ✅ Đang dùng ảnh đại diện tùy chỉnh
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <?php elseif ($tab === 'password'): ?>
      <!-- ── Tab đổi mật khẩu ── -->
      <div class="st-form-card">
        <div class="st-form-header">
          <h1>🔒 Đổi mật khẩu</h1>
          <p>Dùng mật khẩu mạnh để bảo vệ tài khoản</p>
        </div>
        <div class="st-form-body">
          <form id="changePasswordForm">

            <div class="mb-4">
              <label class="form-label">Mật khẩu hiện tại *</label>
              <div class="st-input-wrap">
                <i class="bi bi-lock st-input-icon"></i>
                <input type="password" name="old_password" id="oldPw"
                       class="form-control st-input-icon-pad"
                       placeholder="Nhập mật khẩu hiện tại" required>
                <button type="button" class="st-pw-toggle"
                        onclick="togglePw('oldPw', this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Mật khẩu mới *</label>
              <div class="st-input-wrap">
                <i class="bi bi-lock-fill st-input-icon"></i>
                <input type="password" name="new_password" id="newPw"
                       class="form-control st-input-icon-pad"
                       placeholder="Ít nhất 6 ký tự" required>
                <button type="button" class="st-pw-toggle"
                        onclick="togglePw('newPw', this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Xác nhận mật khẩu mới *</label>
              <div class="st-input-wrap">
                <i class="bi bi-lock-fill st-input-icon"></i>
                <input type="password" name="confirm_password" id="confirmPw"
                       class="form-control st-input-icon-pad"
                       placeholder="Nhập lại mật khẩu mới" required>
                <button type="button" class="st-pw-toggle"
                        onclick="togglePw('confirmPw', this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="st-form-actions">
              <button type="submit" class="btn btn-primary" id="btnChangePw">
                <i class="bi bi-shield-check"></i> Đổi mật khẩu
              </button>
            </div>

          </form>
        </div>
      </div>

      <script>
      document.getElementById('changePasswordForm')?.addEventListener('submit', async (e) => {
          e.preventDefault();
          const btn = document.getElementById('btnChangePw');
          const formData = new FormData(e.target);
          const data = Object.fromEntries(formData.entries());

          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang đổi...';

          try {
              const res = await apiFetch('/api/account/changePassword', {
                  method: 'POST',
                  body: JSON.stringify(data)
              });
              const result = await res.json();
              if (result.success) {
                  showToast('Đã đổi mật khẩu thành công!', '🔒');
                  e.target.reset(); // Clear form
              } else {
                  let msg = result.message || 'Lỗi đổi mật khẩu';
                  if (result.errors) msg = Object.values(result.errors).join('\n');
                  alert(msg);
              }
          } catch (e) { alert('Lỗi kết nối'); } finally {
              btn.disabled = false;
              btn.innerHTML = '<i class="bi bi-shield-check"></i> Đổi mật khẩu';
          }
      });
      </script>

      <?php endif; ?>

      <script>
      // ─── Avatar Sidebar ───
      document.getElementById('avatarFileInput')?.addEventListener('change', function() {
          if (this.files && this.files[0]) document.getElementById('avatarForm').submit();
      });

      // ─── Avatar Detail ───
      const avInput2 = document.getElementById('avatarFileInput2');
      avInput2?.addEventListener('change', function(e) {
          const file = e.target.files[0];
          if (!file) return;

          // Preview
          const reader = new FileReader();
          reader.onload = function(re) {
              const previewImg = document.getElementById('avatarNewImg');
              const previewBox = document.getElementById('avatarNewPreview');
              const saveBtn = document.getElementById('avatarSaveBtn');

              if (previewImg) previewImg.src = re.target.result;
              if (previewBox) previewBox.style.display = 'flex';
              
              const nameEl = document.getElementById('avatarNewName');
              const sizeEl = document.getElementById('avatarNewSize');
              if (nameEl) nameEl.textContent = file.name;
              if (sizeEl) sizeEl.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
              
              if (saveBtn) saveBtn.style.display = 'inline-block';
              
              // Copy file to hidden form
              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(file);
              const hiddenInput = document.getElementById('avatarFileHidden');
              if (hiddenInput) hiddenInput.files = dataTransfer.files;
          }
          reader.readAsDataURL(file);
      });

      document.getElementById('avatarRemoveBtn')?.addEventListener('click', () => {
          const previewBox = document.getElementById('avatarNewPreview');
          const saveBtn = document.getElementById('avatarSaveBtn');
          if (previewBox) previewBox.style.display = 'none';
          if (saveBtn) saveBtn.style.display = 'none';
          
          if (avInput2) avInput2.value = '';
          const hiddenInput = document.getElementById('avatarFileHidden');
          if (hiddenInput) hiddenInput.value = '';
      });
      </script>


    </div>
  </div>

</div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
