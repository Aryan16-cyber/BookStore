<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();
$adminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — BookHaven</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/bookhaven/assets/css/style.css">
</head>
<body>
<nav>
  <a class="nav-logo" href="/bookhaven/index.php">Book<span>Haven</span></a>
  <div class="nav-links">
    <a href="/bookhaven/index.php">← Storefront</a>
    <a href="/bookhaven/admin/index.php" class="<?= $adminPage==='index.php'?'active':'' ?>">Dashboard</a>
    <a href="/bookhaven/admin/books.php" class="<?= $adminPage==='books.php'?'active':'' ?>">Books</a>
    <a href="/bookhaven/admin/orders.php" class="<?= $adminPage==='orders.php'?'active':'' ?>">Orders</a>
    <a href="/bookhaven/admin/customers.php" class="<?= $adminPage==='customers.php'?'active':'' ?>">Customers</a>
    <a href="/bookhaven/admin/categories.php" class="<?= $adminPage==='categories.php'?'active':'' ?>">Categories</a>
    <a href="/bookhaven/admin/reports.php" class="<?= $adminPage==='reports.php'?'active':'' ?>">Reports</a>
  </div>
  <div class="nav-right">
    <span class="user-btn" style="cursor:default;border-color:var(--amber);color:var(--amber);">⚙️ Admin</span>
    <a class="user-btn danger-btn" href="/bookhaven/logout.php">Logout</a>
  </div>
</nav>
<?php flash(); ?>
