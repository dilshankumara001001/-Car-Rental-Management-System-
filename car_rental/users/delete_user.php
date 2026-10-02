<?php
require_once("../config/auth.php"); require_admin();
include("../config/db.php");
include("../includes/functions.php");

$id = (int)($_GET['id'] ?? 0);

if ($id && $id != $_SESSION['user_id']) {
    $u = db_row($conn, "SELECT username FROM users WHERE user_id=?", [$id], 'i');
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    set_flash('success', "User '{$u['username']}' deleted");
} else {
    set_flash('error', "Cannot delete yourself!");
}

redirect('users/manage_users.php');