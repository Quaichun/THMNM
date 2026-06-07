<?php
require_once 'app/helpers/SessionHelper.php';
SessionHelper::requireAdmin();
$flash = SessionHelper::getFlash('success');
$error = SessionHelper::getFlash('error');

$totalUsers = count($users);
$totalAdmins = 0;
$totalNormalUsers = 0;
$totalLocked = 0;
foreach ($users as $u) {
    if (($u->role ?? 'user') === 'admin') $totalAdmins++;
    else $totalNormalUsers++;
    if (($u->status ?? 'active') !== 'active') $totalLocked++;
}
?>
<?php include 'app/views/shares/header.php'; ?>

<div class="st-page st-users-page">
<div class="st-container">
  <div class="st-page-head fade-up">
    <div>
      <h1>👥 Quản lý người dùng</h1>
      <div class="st-breadcrumb">
        <a href="/Product">Trang chủ</a> › <span>Quản lý</span> › <span>Người dùng</span>
      </div>
    </div>
  </div>

  <?php if ($flash): ?><div class="alert alert-success fade-up"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger fade-up"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

  <div class="st-users-kpi-grid fade-up">
    <div class="st-users-kpi-card">
      <div class="st-users-kpi-icon blue"><i class="bi bi-people-fill"></i></div>
      <div><div class="st-users-kpi-title">Tổng người dùng</div><div class="st-users-kpi-value"><?php echo $totalUsers; ?></div></div>
    </div>
    <div class="st-users-kpi-card">
      <div class="st-users-kpi-icon green"><i class="bi bi-person"></i></div>
      <div><div class="st-users-kpi-title">Người dùng</div><div class="st-users-kpi-value"><?php echo $totalNormalUsers; ?></div></div>
    </div>
    <div class="st-users-kpi-card">
      <div class="st-users-kpi-icon purple"><i class="bi bi-shield-check"></i></div>
      <div><div class="st-users-kpi-title">Quản trị viên</div><div class="st-users-kpi-value"><?php echo $totalAdmins; ?></div></div>
    </div>
    <div class="st-users-kpi-card">
      <div class="st-users-kpi-icon orange"><i class="bi bi-lock"></i></div>
      <div><div class="st-users-kpi-title">Tài khoản bị khóa</div><div class="st-users-kpi-value"><?php echo $totalLocked; ?></div></div>
    </div>
  </div>

  <div class="st-users-panel fade-up">
    <div class="st-users-toolbar">
      <input id="userSearchInput" type="text" class="form-control" placeholder="Tìm kiếm theo tên, email...">
      <select id="roleFilter" class="form-select">
        <option value="">-- Tất cả vai trò --</option>
        <option value="user">Người dùng</option>
        <option value="admin">Admin</option>
      </select>
      <select id="statusFilter" class="form-select">
        <option value="">-- Tất cả trạng thái --</option>
        <option value="active">Đang hoạt động</option>
        <option value="locked">Đã khóa</option>
      </select>
    </div>

    <div class="table-responsive st-users-table-wrap">
      <table class="table st-users-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Trạng thái</th>
            <th>Xác thực email</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody id="usersTableBody">
          <?php foreach ($users as $u): ?>
          <?php
            $status = $u->status ?? 'active';
            $role = $u->role ?? 'user';
          ?>
          <tr class="<?php echo $status === 'active' ? 'row-active' : 'row-locked'; ?>" data-role="<?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>" data-status="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
            <td><div class="st-td"><?php echo (int)$u->id; ?></div></td>
            <td>
              <div class="st-td">
                <div class="st-user-cell">
                  <?php
                    $palette = ['blue', 'green', 'amber', 'purple', 'teal', 'coral', 'pink'];
                    $colorClass = $palette[((int)$u->id) % count($palette)];
                  ?>
                  <div class="st-user-av <?php echo $colorClass; ?>">
                    <?php if (!empty($u->avatar)): ?>
                      <img src="/<?php echo htmlspecialchars($u->avatar, ENT_QUOTES, 'UTF-8'); ?>" alt="avatar">
                    <?php else: ?>
                      <?php echo strtoupper(mb_substr($u->fullname ?: $u->username, 0, 1, 'UTF-8')); ?>
                    <?php endif; ?>
                  </div>
                  <div>
                    <div class="st-user-name"><?php echo htmlspecialchars($u->fullname, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="st-user-un">@<?php echo htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                </div>
              </div>
            </td>
            <td><div class="st-td"><?php echo htmlspecialchars($u->email ?? '', ENT_QUOTES, 'UTF-8'); ?></div></td>
            <td>
              <div class="st-td">
                <form method="POST" action="/Account/updateUserRole" class="st-users-inline-form">
                  <input type="hidden" name="id" value="<?php echo (int)$u->id; ?>">
                  <select name="role" class="form-select form-select-sm">
                    <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>Người dùng</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                  </select>
                  <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i></button>
                </form>
              </div>
            </td>
            <td>
              <div class="st-td">
                <span class="st-ubadge <?php echo $status === 'active' ? 'active' : 'locked'; ?>">
                  <?php echo $status === 'active' ? 'Đang hoạt động' : 'Đã khóa'; ?>
                </span>
              </div>
            </td>
            <td>
              <div class="st-td">
                <span class="st-ubadge <?php echo !empty($u->email_verified_at) ? 'verified' : 'unverified'; ?>">
                  <?php echo !empty($u->email_verified_at) ? 'Đã xác thực' : 'Chưa xác thực'; ?>
                </span>
              </div>
            </td>
            <td><div class="st-td"><?php echo !empty($u->created_at) ? date('d/m/Y', strtotime($u->created_at)) : '-'; ?></div></td>
            <td>
              <div class="st-td" style="display: flex; gap: 5px;">
                <a href="/Account/editUser/<?php echo (int)$u->id; ?>" class="btn btn-sm btn-warning" title="Sửa">
                  <i class="bi bi-pencil"></i>
                </a>
                
                <form method="POST" action="/Account/toggleUserStatus" style="display: inline-block;">
                  <input type="hidden" name="id" value="<?php echo (int)$u->id; ?>">
                  <button class="btn btn-sm <?php echo $status === 'active' ? 'btn-outline-danger' : 'btn-outline-success'; ?>" type="submit" title="<?php echo $status === 'active' ? 'Khóa' : 'Mở khóa'; ?>">
                    <i class="bi <?php echo $status === 'active' ? 'bi-lock' : 'bi-unlock'; ?>"></i>
                  </button>
                </form>

                <a href="/Account/deleteUser/<?php echo (int)$u->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này? Thao tác này không thể hoàn tác!')" title="Xóa">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="st-users-note fade-up">
    <h4>🔎 Chú thích trạng thái</h4>
    <p><strong>Đang hoạt động:</strong> Người dùng có thể đăng nhập và sử dụng hệ thống.</p>
    <p><strong>Đã khóa:</strong> Người dùng bị khóa tài khoản, không thể đăng nhập.</p>
    <p><strong>Đã xác thực:</strong> Email đã được xác thực.</p>
    <p><strong>Chưa xác thực:</strong> Email chưa được xác thực.</p>
  </div>
</div>
</div>

<script>
  (function () {
    const input = document.getElementById('userSearchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const rows = Array.from(document.querySelectorAll('#usersTableBody tr'));

    function applyFilter() {
      const q = (input.value || '').toLowerCase().trim();
      const role = roleFilter.value;
      const status = statusFilter.value;

      rows.forEach((row) => {
        const text = row.innerText.toLowerCase();
        const okQ = !q || text.includes(q);
        const okRole = !role || row.dataset.role === role;
        const okStatus = !status || row.dataset.status === status;
        row.style.display = (okQ && okRole && okStatus) ? '' : 'none';
      });
    }

    input.addEventListener('input', applyFilter);
    roleFilter.addEventListener('change', applyFilter);
    statusFilter.addEventListener('change', applyFilter);
  })();
</script>

<?php include 'app/views/shares/footer.php'; ?>
