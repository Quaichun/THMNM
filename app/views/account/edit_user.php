<?php include 'app/views/shares/header.php'; ?>

<div class="st-container" style="padding-top: 40px; padding-bottom: 40px;">
    <div class="st-page-head fade-up">
        <h1>👤 Chỉnh sửa người dùng</h1>
        <div class="st-breadcrumb">
            <a href="/Product">Trang chủ</a> › <a href="/Account/users">Quản lý người dùng</a> › <span>Sửa</span>
        </div>
    </div>

    <div class="st-form-card fade-up" style="max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <form method="POST" action="/Account/saveUserEdit">
            <input type="hidden" name="id" value="<?php echo $user->id; ?>">
            
            <div class="mb-3">
                <label class="form-label">Họ tên</label>
                <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user->fullname, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Vai trò</label>
                <select name="role" class="form-select">
                    <option value="user" <?php echo $user->role === 'user' ? 'selected' : ''; ?>>Người dùng</option>
                    <option value="admin" <?php echo $user->role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="active" <?php echo ($user->status ?? 'active') === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                    <option value="locked" <?php echo ($user->status ?? 'active') === 'locked' ? 'selected' : ''; ?>>Đã khóa</option>
                </select>
            </div>

            <div class="st-form-actions" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                <a href="/Account/users" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
