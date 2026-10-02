<?php
require_once("../config/auth.php"); require_admin();
include("../config/db.php");
include("../includes/functions.php");

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('users/manage_users.php');

$user = db_row($conn, "SELECT * FROM users WHERE user_id=?", [$id], 'i');
if (!$user) die("User not found");

$msg = "";
if (isset($_POST['update'])) {
    $u = trim($_POST['username']);
    $e = trim($_POST['email']);
    $r = $_POST['role'];
    $p = trim($_POST['password']);

    if ($p !== '') {
        if (strlen($p) < 4) {
            $msg = "Password must be at least 4 characters";
        } else {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn,
              "UPDATE users SET username=?, email=?, role=?, password=? WHERE user_id=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $u, $e, $r, $hash, $id);
        }
    } else {
        $stmt = mysqli_prepare($conn,
          "UPDATE users SET username=?, email=?, role=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $u, $e, $r, $id);
    }

    if (empty($msg) && mysqli_stmt_execute($stmt)) {
        set_flash('success', "User updated!");
        redirect('users/manage_users.php');
    } elseif (empty($msg)) {
        $msg = "Error: " . mysqli_error($conn);
    }
}

include("../includes/header.php");
?>
<div class="container"><div class="card" style="max-width:500px">
  <h2>✏️ Edit User — <?= e($user['username']) ?></h2>
  <?php if ($msg): ?><p style="color:#dc2626"><?= e($msg) ?></p><?php endif; ?>

  <form method="post">
    <label>Username *</label>
    <input name="username" value="<?= e($user['username']) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>">

    <label>Role *</label>
    <select name="role" required>
      <option value="staff" <?= $user['role']==='staff'?'selected':'' ?>>Staff</option>
      <option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>Admin</option>
    </select>

    <label>New Password (හිස්ව තියන්න = වෙනස් නොකරන්න)</label>
    <input type="text" name="password" minlength="4">

    <button class="btn" name="update" style="width:100%">Update</button>
    <a class="btn" href="manage_users.php" style="background:#6b7280;width:100%;text-align:center;margin-top:8px">Cancel</a>
  </form>
</div></div>