<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// ── Handle POST actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $book_id = (int)($_POST['book_id'] ?? 0);
    $qty     = max(1, (int)($_POST['quantity'] ?? 1));

    if ($action === 'add') {
        if (!isLoggedIn()) { redirect('login.php', 'Please log in to add to cart.', 'error'); }
        $uid = (int)$_SESSION['user_id'];
        // Check stock
        $stock = $conn->query("SELECT stock FROM books WHERE book_id=$book_id")->fetch_assoc()['stock'] ?? 0;
        if ($stock < 1) { redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php', 'Sorry, this book is out of stock.', 'error'); }
        $stmt = $conn->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->bind_param("iiii", $uid, $book_id, $qty, $qty);
        $stmt->execute();
        redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php', '✅ Book added to cart!');
    }

    if ($action === 'update') {
        if (!isLoggedIn()) redirect('login.php');
        $uid    = (int)$_SESSION['user_id'];
        $newQty = max(1, (int)($_POST['quantity'] ?? 1));
        $conn->query("UPDATE cart SET quantity=$newQty WHERE user_id=$uid AND book_id=$book_id");
        redirect('cart.php', 'Cart updated.');
    }

    if ($action === 'remove') {
        if (!isLoggedIn()) redirect('login.php');
        $uid = (int)$_SESSION['user_id'];
        $conn->query("DELETE FROM cart WHERE user_id=$uid AND book_id=$book_id");
        redirect('cart.php', 'Item removed from cart.');
    }

    if ($action === 'clear') {
        if (!isLoggedIn()) redirect('login.php');
        $uid = (int)$_SESSION['user_id'];
        $conn->query("DELETE FROM cart WHERE user_id=$uid");
        redirect('cart.php', 'Cart cleared.');
    }
}

// ── Render ──
require_once 'includes/header.php';

$cartItems = [];
$subtotal  = 0;

if (isLoggedIn()) {
    $uid  = (int)$_SESSION['user_id'];
    $rows = $conn->query("SELECT c.*, b.title, b.author, b.price, b.cover_icon, b.cover_bg, b.stock FROM cart c JOIN books b ON c.book_id=b.book_id WHERE c.user_id=$uid");
    while ($row = $rows->fetch_assoc()) {
        $cartItems[] = $row;
        $subtotal   += $row['price'] * $row['quantity'];
    }
}

$tax   = round($subtotal * 0.05);
$total = $subtotal + $tax;
?>

<div class="cart-page">
  <h2 class="page-title">🛒 Your Cart</h2>

  <?php if (!isLoggedIn()): ?>
    <div class="empty-state">
      <div class="empty-icon">🔐</div>
      <h3>Please Sign In</h3>
      <p>You need to be logged in to view your cart.</p>
      <a class="btn-primary" href="login.php">Sign In</a>
    </div>

  <?php elseif (empty($cartItems)): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <h3>Your cart is empty</h3>
      <p>Browse our collection and add some books!</p>
      <a class="btn-primary" href="shop.php">Browse Books</a>
    </div>

  <?php else: ?>
    <div class="cart-grid">
      <!-- ITEMS -->
      <div class="cart-items">
        <?php foreach($cartItems as $item): ?>
          <div class="cart-item">
            <div class="ci-icon" style="background:<?= $item['cover_bg'] ?>"><?= $item['cover_icon'] ?></div>
            <div class="ci-info">
              <div class="ci-title"><?= htmlspecialchars($item['title']) ?></div>
              <div class="ci-author">by <?= htmlspecialchars($item['author']) ?></div>
              <div class="ci-price">₹<?= number_format($item['price'], 0) ?> each</div>
            </div>
            <div class="ci-controls">
              <!-- Update qty -->
              <form method="POST" action="cart.php" class="ci-qty">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="book_id" value="<?= $item['book_id'] ?>">
                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>"
                  onchange="this.form.submit()" style="width:52px;text-align:center;border:2px solid var(--border);border-radius:8px;padding:.35rem;font-size:.9rem;">
              </form>
              <!-- Remove -->
              <form method="POST" action="cart.php">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="book_id" value="<?= $item['book_id'] ?>">
                <button type="submit" class="btn-remove" title="Remove">🗑️</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Clear cart -->
        <form method="POST" action="cart.php" style="text-align:right;">
          <input type="hidden" name="action" value="clear">
          <button type="submit" style="background:none;border:none;color:var(--rust);cursor:pointer;font-size:.85rem;font-weight:600;">Clear Cart ✕</button>
        </form>
      </div>

      <!-- ORDER SUMMARY -->
      <div class="order-summary">
        <h3>Order Summary</h3>
        <?php foreach($cartItems as $item): ?>
          <div class="sum-row">
            <span><?= htmlspecialchars($item['title']) ?> ×<?= $item['quantity'] ?></span>
            <span>₹<?= number_format($item['price'] * $item['quantity'], 0) ?></span>
          </div>
        <?php endforeach; ?>
        <div class="sum-row"><span>Subtotal</span><span>₹<?= number_format($subtotal, 0) ?></span></div>
        <div class="sum-row"><span>Shipping</span><span style="color:#6ee56e;">Free ✓</span></div>
        <div class="sum-row"><span>Tax (5%)</span><span>₹<?= number_format($tax, 0) ?></span></div>
        <div class="sum-row total-row"><span>Total</span><span>₹<?= number_format($total, 0) ?></span></div>
        <a class="btn-checkout" href="checkout.php">Proceed to Checkout →</a>
        <a class="btn-clear" href="shop.php">← Continue Shopping</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
