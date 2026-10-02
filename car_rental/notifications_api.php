<?php
require_once("config/auth.php");
require_once("config/db.php");
require_once("includes/functions.php");

require_login();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$uid    = $_SESSION['user_id'];

if ($action === 'list') {
    $rows = db_all($conn,
      "SELECT notif_id, title, message, is_read,
              DATE_FORMAT(created_at, '%d %b %H:%i') AS created_at
       FROM notifications
       WHERE user_id=? OR user_id IS NULL
       ORDER BY notif_id DESC LIMIT 20", [$uid], 'i');
    echo json_encode($rows);
    exit();
}

if ($action === 'read_all') {
    mysqli_query($conn, "UPDATE notifications SET is_read=1
                         WHERE user_id=$uid OR user_id IS NULL");
    echo json_encode(['ok' => true]);
    exit();
}

if ($action === 'toggle_theme') {
    $row = db_row($conn, "SELECT theme FROM users WHERE user_id=?", [$uid], 'i');
    $new = ($row['theme'] ?? 'light') === 'dark' ? 'light' : 'dark';
    $upd = mysqli_prepare($conn, "UPDATE users SET theme=? WHERE user_id=?");
    mysqli_stmt_bind_param($upd, "si", $new, $uid);
    mysqli_stmt_execute($upd);
    echo json_encode(['theme' => $new]);
    exit();
}

echo json_encode(['error' => 'Unknown action']);