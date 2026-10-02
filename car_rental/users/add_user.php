<?php
require_once("../config/auth.php"); require_admin();
include("../config/db.php");
include("../includes/functions.php");

$msg = "";
if (isset($_POST['save'])) {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);
    $e = trim($_POST['email']);
    $r = $_POST['role'];

    if (strlen($p) < 4) {
        $msg = "Password must be at least 4 characters";
    } else {
        $hash = password_hash($p, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn,
          "INSERT INTO users (username, password, email, role) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssss", $u, $hash, $e, $r);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', "User '$u' created successfully");
            redirect('users/manage_users.php');
        } else {
            $msg = "Error: " . mysqli_error($conn);
        }
    }
}
include("../includes/header.php");
?>
<div class="container"><div class="card" style="max-width:500px">
  <h2>➕ Add User</h2>
  <?php if ($msg): ?><p style="color:#dc2626"><?= e($msg) ?></p><?php endif; ?>

  <form method="post">
    <label>Username *</label>
    <input name="username" required autofocus>

    <label>Password * (min 4 chars)</label>
    <input type="text" name="password" required minlength="4">

    <label>Email</label>
    <input type="email" name="email">

    <label>Role *</label>
    <select name="role" required>
      <option value="staff">Staff</option>
      <option value="admin">Admin</option>
    </select>

    <button class="btn" name="save" style="width:100%">Create User</button>
    <a class="btn" href="manage_users.php" style="background:#6b7280;width:100%;text-align:center;margin-top:8px">Cancel</a>
  </form>
</div></div>