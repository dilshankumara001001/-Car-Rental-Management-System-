<?php
require_once("../config/auth.php");
require_login();
include("../config/db.php");
/* ⚠️ functions.php include කරන්න එපා — header.php එකෙන් auto-load වෙනවා */

$msg = "";

if (isset($_POST['rent'])) {
    $car_id  = (int)$_POST['car'];
    $cust_id = (int)$_POST['cust'];

    $chk = mysqli_prepare($conn, "SELECT status FROM cars WHERE car_id=?");
    mysqli_stmt_bind_param($chk, "i", $car_id);
    mysqli_stmt_execute($chk);
    $car = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));

    if ($car && $car['status'] === 'Available') {
        mysqli_begin_transaction($conn);
        try {
            $s1 = mysqli_prepare($conn,
                "INSERT INTO rentals (car_id,customer_id,rental_date,rental_status)
                 VALUES (?,?,CURDATE(),'Rented')");
            mysqli_stmt_bind_param($s1, "ii", $car_id, $cust_id);
            mysqli_stmt_execute($s1);

            $s2 = mysqli_prepare($conn,
                "UPDATE cars SET status='Rented' WHERE car_id=?");
            mysqli_stmt_bind_param($s2, "i", $car_id);
            mysqli_stmt_execute($s2);

            mysqli_commit($conn);
            header("Location: view_rentals.php");
            exit();
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            $msg = "Rental failed!";
        }
    } else {
        $msg = "Car not available.";
    }
}

include("../includes/header.php");
?>
<div class="container"><div class="card" style="max-width:600px">
  <h2>🔑 Rent Car</h2>

  <?php if ($msg): ?>
    <p style="color:#dc2626"><?= e($msg) ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Customer</label>
    <select name="cust" required>
      <?php
      $c = mysqli_query($conn, "SELECT customer_id,name FROM customers ORDER BY name");
      while ($r = mysqli_fetch_assoc($c))
          echo "<option value='{$r['customer_id']}'>" . e($r['name']) . "</option>";
      ?>
    </select>

    <label>Available Car</label>
    <select name="car" required>
      <?php
      $c = mysqli_query($conn,
          "SELECT car_id,car_name,registration_no,rental_price
           FROM cars WHERE status='Available' ORDER BY car_name");
      if (mysqli_num_rows($c) === 0)
          echo "<option value=''>No cars available</option>";
      while ($r = mysqli_fetch_assoc($c))
          echo "<option value='{$r['car_id']}'>" . e($r['car_name'])
             . " (" . e($r['registration_no']) . ") - Rs."
             . number_format($r['rental_price'],2) . "/day</option>";
      ?>
    </select>

    <button class="btn" name="rent">Rent Now</button>
  </form>
</div></div>