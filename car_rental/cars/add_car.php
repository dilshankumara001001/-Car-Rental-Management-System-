<?php
require_once("../config/auth.php"); require_admin(); include("../config/db.php");
$msg = "";
if (isset($_POST['save'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO cars (car_name,brand,model,year,registration_no,rental_price,status) VALUES (?,?,?,?,?,?,'Available')");
    mysqli_stmt_bind_param($stmt, "sssisd", $_POST['name'], $_POST['brand'], $_POST['model'], (int)$_POST['year'], $_POST['reg'], (float)$_POST['price']);
    if (mysqli_stmt_execute($stmt)) { header("Location: view_car.php"); exit(); }
    else $msg = "Error: " . mysqli_error($conn);
}
include("../includes/header.php");
?>
<div class="container"><div class="card" style="max-width:600px">
  <h2>➕ Add Car</h2>
  <?php if ($msg): ?><p style="color:#dc2626"><?= e($msg) ?></p><?php endif; ?>
  <form method="post">
    <input name="name" placeholder="Car Name" required>
    <input name="brand" placeholder="Brand" required>
    <input name="model" placeholder="Model" required>
    <input name="year" placeholder="Year" type="number" required>
    <input name="reg" placeholder="Registration No" required>
    <input name="price" placeholder="Price per Day" type="number" step="0.01" required>
    <button class="btn" name="save">Save Car</button>
  </form>
</div></div>