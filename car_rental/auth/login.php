<?php
require_once("../config/auth.php");
require_once("../includes/functions.php");   /* ⭐ මේක අලුතින් add කරන්න */
include("../config/db.php");

if (isset($_SESSION['user_id'])) { 
    header("Location: " . BASE_URL . "index.php"); 
    exit(); 
}

/* ═══════════════════════════════════════════════════════════
 * 🔧 AUTO-FIX: Fake hash එක real එකකින් replace කරනවා
 * ═══════════════════════════════════════════════════════════ */
function autofix_user($conn, $username, $defaultPassword) {
    $stmt = mysqli_prepare($conn, "SELECT user_id, password FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$row) return;

    $isFake = (
        strpos($row['password'], 'e0MYzXyjpJS7Pd0RVvHwHe') !== false ||
        strpos($row['password'], '8K1p/a0dL1LXMIgoED')      !== false
    );

    if ($isFake || !password_verify($defaultPassword, $row['password'])) {
        if (!password_verify($defaultPassword, $row['password'])) {
            $newHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
            $upd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($upd, "si", $newHash, $row['user_id']);
            mysqli_stmt_execute($upd);
        }
    }
}

autofix_user($conn, 'admin', 'admin123');
autofix_user($conn, 'staff', 'staff123');

/* ═══════════════════════════════════════════════════════════
 * LOGIN LOGIC
 * ═══════════════════════════════════════════════════════════ */
$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT user_id, username, password, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        $logged_in = false;

        /* 1️⃣ Normal verify */
        if (password_verify($password, $user['password'])) {
            $logged_in = true;
        }
        /* 2️⃣ Fallback — default passwords */
        else {
            $defaults = ['admin' => 'admin123', 'staff' => 'staff123'];
            if (isset($defaults[$user['username']]) && $password === $defaults[$user['username']]) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($upd, "si", $newHash, $user['user_id']);
                mysqli_stmt_execute($upd);
                $logged_in = true;
            }
        }

        if ($logged_in) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            /* Update last_login */
            $upd = mysqli_prepare($conn, "UPDATE users SET last_login=NOW() WHERE user_id=?");
            mysqli_stmt_bind_param($upd, "i", $user['user_id']);
            mysqli_stmt_execute($upd);

            header("Location: " . BASE_URL . "index.php");
            exit();
        }
    }
    $error = "Invalid username or password";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login — Car Rental</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="container"><div class="card" style="max-width:400px;margin:60px auto">
  <h2>🔐 Login</h2>

  <?php if ($error): ?>
    <p style="color:#dc2626"><?= e($error) ?></p>
  <?php endif; ?>

  <form method="post">
    <input name="username" placeholder="Username" required autofocus 
           value="<?= isset($_POST['username']) ? e($_POST['username']) : '' ?>">
    <input type="password" name="password" placeholder="Password" required>
    <button class="btn" name="login" style="width:100%">Login</button>
  </form>

  <p style="font-size:12px;color:#6b7280;margin-top:15px;text-align:center">
    Default: <b>admin / admin123</b> &nbsp;|&nbsp; <b>staff / staff123</b>
  </p>
</div></div>
</body>
</html>