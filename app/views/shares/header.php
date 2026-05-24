<?php
require_once 'app/helpers/SessionHelper.php';
SessionHelper::start();

$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) $cartCount += $item['quantity'];
}

$isLoggedIn = SessionHelper::isLoggedIn();
$isAdmin    = SessionHelper::isAdmin();
$fullname   = SessionHelper::getFullname();
$username   = SessionHelper::getUsername();
$avatar     = SessionHelper::getAvatar();
$avatarUrl  = !empty($avatar)
    ? '/' . $avatar
    : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShopTech</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<nav class="st-navbar">

  <!-- Logo -->
  <a href="/Product" class="st-logo">
    <div class="st-logo-icon">🛍️</div>
    ShopTech
  </a>

  <!-- Search -->
  <div class="st-search">
    <input type="text" placeholder="Tìm kiếm sản phẩm...">
    <button type="button"><i class="bi bi-search"></i></button>
    <div class="st-search-dropdown"></div>
  </div>

  <!-- Nav links -->
  <ul class="st-nav-links" id="navLinks">

    <!-- Trang chủ -->
    <li>
      <a href="/Product">🏠 Trang chủ</a>
    </li>

    <!-- Sản phẩm -->
    <li class="st-dropdown">
      <a href="#" class="st-dropdown-toggle" onclick="return false;">
        📦 Sản phẩm <i class="bi bi-chevron-down st-chev"></i>
      </a>
      <ul class="st-dropdown-menu">
        <li><a href="/Product/list">
          <i class="bi bi-grid"></i> Danh sách sản phẩm
        </a></li>
        <li><a href="/Category/list">
          <i class="bi bi-tags"></i> Danh mục sản phẩm
        </a></li>
      </ul>
    </li>

    <!-- Quản lý — chỉ admin -->
    <?php if ($isAdmin): ?>
    <li class="st-dropdown">
      <a href="#" class="st-dropdown-toggle" onclick="return false;">
        ⚙️ Quản lý <i class="bi bi-chevron-down st-chev"></i>
      </a>
      <ul class="st-dropdown-menu">
        <li><a href="/Product/add">
          <i class="bi bi-plus-circle"></i> Thêm sản phẩm
        </a></li>
        <li><a href="/Category/add">
          <i class="bi bi-folder-plus"></i> Thêm danh mục
        </a></li>
        <li><a href="/Category/list">
          <i class="bi bi-pencil-square"></i> Quản lý danh mục
        </a></li>
      </ul>
    </li>
    <?php endif; ?>

    <!-- Mua hàng -->
    <li class="st-dropdown">
      <a href="#" class="st-dropdown-toggle" onclick="return false;">
        🛒 Mua hàng <i class="bi bi-chevron-down st-chev"></i>
      </a>
      <ul class="st-dropdown-menu">
        <li><a href="/Product/cart">
          <i class="bi bi-cart3"></i> Giỏ hàng
          <?php if ($cartCount > 0): ?>
            <span class="st-dd-badge"><?php echo $cartCount; ?></span>
          <?php endif; ?>
        </a></li>
        <li><a href="/Product/checkout">
          <i class="bi bi-bag-check"></i> Thanh toán
        </a></li>
        <li><a href="/Product/myOrders">
          <i class="bi bi-receipt"></i> Đơn hàng của tôi
        </a></li>
      </ul>
    </li>

    <!-- Tài khoản -->
    <li class="st-dropdown">
      <a href="#" class="st-dropdown-toggle" onclick="return false;">

        <?php if ($isLoggedIn): ?>
          <!-- Avatar nhỏ trên navbar -->
          <?php if ($avatarUrl): ?>
            <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>"
                 class="st-nav-avatar-img" alt="avatar">
          <?php else: ?>
            <div class="st-nav-avatar">
              <?php echo strtoupper(mb_substr($fullname, 0, 1, 'UTF-8')); ?>
            </div>
          <?php endif; ?>
          <?php echo htmlspecialchars(
              explode(' ', trim($fullname))[count(explode(' ', trim($fullname))) - 1],
              ENT_QUOTES, 'UTF-8'
          ); ?>
        <?php else: ?>
          👤 Tài khoản
        <?php endif; ?>

        <i class="bi bi-chevron-down st-chev"></i>
      </a>

      <ul class="st-dropdown-menu">
        <?php if ($isLoggedIn): ?>

          <!-- Header dropdown: avatar lớn hơn -->
          <li class="st-dd-user-info">
            <div class="st-dd-avatar-wrap">
              <?php if ($avatarUrl): ?>
                <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>"
                     class="st-dd-avatar-img" alt="avatar">
              <?php else: ?>
                <div class="st-dd-avatar">
                  <?php echo strtoupper(mb_substr($fullname, 0, 1, 'UTF-8')); ?>
                </div>
              <?php endif; ?>
            </div>
            <div>
              <strong><?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?></strong>
              <small>@<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></small>
            </div>
          </li>

          <li class="st-dd-divider"></li>

          <li><a href="/Account/profile">
            <i class="bi bi-person-fill"></i> Hồ sơ cá nhân
          </a></li>
          <li><a href="/Account/profile?tab=password">
            <i class="bi bi-shield-lock"></i> Đổi mật khẩu
          </a></li>
          <li><a href="/Product/myOrders">
            <i class="bi bi-receipt"></i> Đơn hàng của tôi
          </a></li>

          <li class="st-dd-divider"></li>

          <li>
            <a href="/Account/logout"
               onclick="return confirm('Bạn có chắc muốn đăng xuất?')"
               style="color:#ef4444">
              <i class="bi bi-box-arrow-right"></i> Đăng xuất
            </a>
          </li>

        <?php else: ?>

          <li><a href="/Account/login">
            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
          </a></li>
          <li><a href="/Account/register">
            <i class="bi bi-person-plus"></i> Đăng ký
          </a></li>

        <?php endif; ?>
      </ul>
    </li>

  </ul>

  <!-- Cart icon -->
  <a href="/Product/cart" class="st-cart-btn" id="cartNavBtn">
    <i class="bi bi-cart3"></i>
    <span class="st-cart-badge" id="cartBadge"
          <?php if ($cartCount === 0) echo 'style="display:none"'; ?>>
      <?php echo $cartCount; ?>
    </span>
  </a>

  <!-- Mobile toggle -->
  <button class="st-toggle" id="navToggle" aria-label="Menu">
    <i class="bi bi-list"></i>
  </button>

</nav>

<!-- Flash toast toàn cục -->
<?php $globalFlash = SessionHelper::getFlash('success'); ?>
<?php if ($globalFlash): ?>
<div id="globalToast" class="st-toast show">
  ✅ <?php echo htmlspecialchars($globalFlash, ENT_QUOTES, 'UTF-8'); ?>
</div>
<script>
  setTimeout(() => {
    const t = document.getElementById('globalToast');
    if (t) t.classList.remove('show');
  }, 3000);
</script>
<?php endif; ?>