<?php
require_once("../config/auth.php"); require_login(); include("../config/db.php");
$msg = "";
if (isset($_POST['save'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO customers (name,address,phone,email,license_no) VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "sssss", $_POST['name'], $_POST['address'], $_POST['phone'], $_POST['email'], $_POST['lic']);
    if (mysqli_stmt_execute($stmt)) { header("Location: view_customer.php"); exit(); }
    else $msg = "Error: " . mysqli_error($conn);
}
include("../includes/header.php");
?>
<div class="container"><div class="card" style="max-width:600px">
  <h2>➕ Add Customer</h2>
  <?php if ($msg): ?><p style="color:#dc2626"><?= e($msg) ?></p><?php endif; ?>
  <form method="post">
    <input name="name" placeholder="Name" required>
    <input name="address" placeholder="Address" required>
    <input name="phone" placeholder="Phone" required>
    <input name="email" placeholder="Email" type="email" required>
    <input name="lic" placeholder="License No" required>
    <button class="btn" name="save">Save Customer</button>
  </form>
</div></div>