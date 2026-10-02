<?php
require_once("../config/auth.php"); require_admin();
include("../config/db.php");
include("../includes/functions.php");

$users = db_all($conn, "SELECT * FROM users ORDER BY user_id");

include("../includes/header.php");
?>
<div class="container">
  <?php show_flash(); ?>

  <div class="card">
    <h2>👤 Users
      <a class="btn" href="add_user.php" style="float:right">+ Add User</a>
    </h2>
    <table>
      <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th>
          <th>Theme</th><th>Last Login</th><th>Action</th></tr>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= (int)$u['user_id'] ?></td>
          <td><b><?= e($u['username']) ?></b></td>
          <td><?= e($u['email'] ?? '—') ?></td>
          <td><span class="badge <?= e($u['role']) ?>"><?= e($u['role']) ?></span></td>
          <td><?= ($u['theme'] ?? 'light') === 'dark' ? '🌙' : '☀️' ?></td>
          <td><?= $u['last_login'] ? fdate($u['last_login'], 'd M Y H:i') : '—' ?></td>
          <td>
            <a class="btn btn-sm" href="edit_user.php?id=<?= (int)$u['user_id'] ?>">✏️</a>
            <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
              <a class="btn btn-sm btn-danger"
                 href="delete_user.php?id=<?= (int)$u['user_id'] ?>"
                 onclick="return confirm('Delete user <?= e($u['username']) ?>?')">🗑️</a>
            <?php else: ?>
              <span style="color:#999;font-size:12px">(you)</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>