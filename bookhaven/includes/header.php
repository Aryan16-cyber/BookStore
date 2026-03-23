<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
$cartCount = getCartCount($conn);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BookHaven – Online Bookstore</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/bookhaven/assets/css/style.css">
</head>
<body>
<nav>
  <a class="nav-logo" href="/bookhaven/index.php">Book<span>Haven</span></a>
  <div class="nav-links">
    <a href="/bookhaven/index.php"      class="<?= $currentPage==='index.php'?'active':'' ?>">Home</a>
    <a href="/bookhaven/shop.php"       class="<?= $currentPage==='shop.php'?'active':'' ?>">Browse</a>
    <?php if(isAdmin()): ?>
    <a href="/bookhaven/admin/index.php" class="<?= strpos($currentPage,'admin')!==false?'active':'' ?>">Admin</a>
    <?php endif; ?>
  </div>
  <div class="nav-right">
    <a class="cart-btn" href="/bookhaven/cart.php">
      🛒 Cart <span class="cart-count"><?= $cartCount ?></span>
    </a>
    <?php if(isLoggedIn()): ?>
      <a class="user-btn" href="/bookhaven/profile.php">👤 <?= htmlspecialchars($_SESSION['first_name']) ?></a>
      <a class="user-btn danger-btn" href="/bookhaven/logout.php">Sign Out</a>
    <?php else: ?>
      <a class="user-btn" href="/bookhaven/login.php">Sign In</a>
    <?php endif; ?>
  </div>
</nav>
<?php flash(); ?>
