<?php
require_once("../config/auth.php"); require_admin(); include("../config/db.php");
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: view_car.php"); exit(); }
if (isset($_POST['update'])) {
    $stmt = mysqli_prepare($conn, "UPDATE cars SET car_name=?,brand=?,model=?,year=?,registration_no=?,rental_price=?,status=? WHERE car_id=?");
    mysqli_stmt_bind_param($stmt, "sssisdsi", $_POST['name'], $_POST['brand'], $_POST['model'], (int)$_POST['year'], $_POST['reg'], (float)$_POST['price'], $_POST['status'], $id);
    mysqli_stmt_execute($stmt);
    header("Location: view_car.php"); exit();
}
$stmt = mysqli_prepare($conn, "SELECT * FROM cars WHERE car_id=?"); mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt);
$car = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$car) die("Car not found");
include("../includes/header.php");
?>
<div class="container"><div class="card" style="max-width:600px">
  <h2>✏️ Edit Car</h2>
  <form method="post">
    <input name="name" value="<?= e($car['car_name']) ?>" required>
    <input name="brand" value="<?= e($car['brand']) ?>" required>
    <input name="model" value="<?= e($car['model']) ?>" required>
    <input name="year" value="<?= e($car['year']) ?>" type="number" required>
    <input name="reg" value="<?= e($car['registration_no']) ?>" required>
    <input name="price" value="<?= e($car['rental_price']) ?>" type="number" step="0.01" required>
    <select name="status">
      <?php foreach (['Available','Rented','Maintenance'] as $s): ?>
        <option value="<?= $s ?>" <?= $car['status']==$s?'selected':'' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn" name="update">Update</button>
  </form>
</div></div>