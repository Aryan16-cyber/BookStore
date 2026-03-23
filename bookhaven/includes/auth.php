<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

function sanitize($conn, $val) {
    return $conn->real_escape_string(trim($val));
}

function getCartCount($conn) {
    if (!isLoggedIn()) return 0;
    $uid = (int)$_SESSION['user_id'];
    $r = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id=$uid");
    $row = $r->fetch_assoc();
    return $row['total'] ?? 0;
}

function redirect($url, $msg = '', $type = 'success') {
    if ($msg) $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    header("Location: $url");
    exit();
}

function flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $cls = $f['type'] === 'error' ? 'flash-error' : 'flash-success';
        echo "<div class='flash {$cls}'>{$f['msg']}</div>";
    }
}
?>
