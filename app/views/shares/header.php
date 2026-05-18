<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShopTech – Sản phẩm công nghệ chất lượng</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════
     NAVBAR
═══════════════════════════════════════════════ -->
<header class="st-navbar">

  <!-- Logo -->
  <a href="/Product/" class="st-logo">
    <div class="st-logo-icon">💻</div>
    ShopTech
  </a>

  <!-- Search -->
  <div class="st-search">
  <input type="text" placeholder="Tìm kiếm sản phẩm...">
  <button type="button"><i class="bi bi-search"></i></button>
  <div class="st-search-dropdown"></div>  <!-- thêm dòng này -->
</div>

  <!-- Nav links -->
  <ul class="st-nav-links">
    <li><a href="/Product/">Trang chủ</a></li>
    <li><a href="/Product/list">Sản phẩm</a></li>
    <li><a href="/Category/list">Danh mục</a></li>
    <li><a href="/Product/add">Thêm SP</a></li>
  </ul>

  <!-- Cart -->
  <a href="/Product/cart" class="st-cart-btn">
    <i class="bi bi-cart3"></i> Giỏ hàng
    <?php if (!empty($_SESSION['cart'])): ?>
      <span class="st-cart-badge"><?php echo count($_SESSION['cart']); ?></span>
    <?php endif; ?>
  </a>

  <!-- Mobile toggle -->
  <button class="st-toggle" aria-label="Menu"><i class="bi bi-list"></i></button>

</header>

<!-- ═══════════════════════════════════════════════
     PAGE CONTENT START
═══════════════════════════════════════════════ -->
<main class="st-page">
<div class="st-container">