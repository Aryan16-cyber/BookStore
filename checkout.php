<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

// Get cart
$uid      = (int)$_SESSION['user_id'];
$cartRows = $conn->query("SELECT c.*, b.title, b.price, b.cover_icon FROM cart c JOIN books b ON c.book_id=b.book_id WHERE c.user_id=$uid");
$cartItems = [];
$subtotal  = 0;
while ($row = $cartRows->fetch_assoc()) { $cartItems[] = $row; $subtotal += $row['price'] * $row['quantity']; }
if (empty($cartItems)) redirect('cart.php', 'Your cart is empty.', 'error');

$tax   = round($subtotal * 0.05);
$total = $subtotal + $tax;

// Handle POST — Place Order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addr   = sanitize($conn, $_POST['address'] ?? '');
    $city   = sanitize($conn, $_POST['city'] ?? '');
    $state  = sanitize($conn, $_POST['state'] ?? '');
    $pin    = sanitize($conn, $_POST['pin'] ?? '');
    $pm     = sanitize($conn, $_POST['payment_method'] ?? 'COD');
    $fullAddr = "$addr, $city, $state - $pin";

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, shipping_addr) VALUES (?,?,?,?)");
    $stmt->bind_param("idss", $uid, $total, $pm, $fullAddr);
    $stmt->execute();
    $orderId = $conn->insert_id;

    // Insert order items & reduce stock
    foreach ($cartItems as $item) {
        $s2 = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?,?,?,?)");
        $s2->bind_param("iiid", $orderId, $item['book_id'], $item['quantity'], $item['price']);
        $s2->execute();
        $conn->query("UPDATE books SET stock=stock-{$item['quantity']}, sold=sold+{$item['quantity']} WHERE book_id={$item['book_id']}");
    }

    // Insert payment record
    $s3 = $conn->prepare("INSERT INTO payments (order_id, user_id, amount, payment_method, status) VALUES (?,?,?,?,'Success')");
    $s3->bind_param("iids", $orderId, $uid, $total, $pm);
    $s3->execute();

    // Clear cart
    $conn->query("DELETE FROM cart WHERE user_id=$uid");

    redirect('profile.php', "🎉 Order #$orderId placed successfully! Thank you.");
}

// Pre-fill user data
$user = $conn->query("SELECT * FROM users WHERE user_id=$uid")->fetch_assoc();
require_once 'includes/header.php';
?>

<div class="checkout-page">
  <h2 class="page-title">Checkout</h2>
  <form method="POST" action="checkout.php">
    <div class="checkout-grid">

      <!-- LEFT: Forms -->
      <div>
        <div class="form-box">
          <h3>📍 Shipping Details</h3>
          <div class="form-row">
            <div class="form-group">
              <label>First Name</label>
              <input type="text" value="<?= htmlspecialchars($user['first_name']) ?>" readonly>
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input type="text" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" readonly>
            </div>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="tel" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 98765 43210">
          </div>
          <div class="form-group">
            <label>Street Address *</label>
            <input type="text" name="address" placeholder="House No., Street, Area" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>City *</label>
              <input type="text" name="city" placeholder="New Delhi" required>
            </div>
            <div class="form-group">
              <label>State *</label>
              <input type="text" name="state" placeholder="Delhi" required>
            </div>
          </div>
          <div class="form-group">
            <label>PIN Code *</label>
            <input type="text" name="pin" placeholder="110001" pattern="[0-9]{6}" required>
          </div>
        </div>

        <div class="form-box">
          <h3>💳 Payment Method</h3>
          <div class="pay-methods">
            <div class="pay-method selected" data-method="Credit Card">💳 Credit Card</div>
            <div class="pay-method" data-method="UPI">📱 UPI</div>
            <div class="pay-method" data-method="Net Banking">🏦 Net Banking</div>
            <div class="pay-method" data-method="COD">💵 COD</div>
          </div>
          <input type="hidden" name="payment_method" id="payment_method" value="Credit Card">
          <div class="form-group">
            <label>Card Number</label>
            <input type="text" placeholder="4242 4242 4242 4242">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Expiry</label>
              <input type="text" placeholder="MM/YY">
            </div>
            <div class="form-group">
              <label>CVV</label>
              <input type="text" placeholder="•••">
            </div>
          </div>
          <button type="submit" class="btn-place">✅ Place Order — ₹<?= number_format($total, 0) ?></button>
        </div>
      </div>

      <!-- RIGHT: Summary -->
      <div>
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
        </div>
      </div>

    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
