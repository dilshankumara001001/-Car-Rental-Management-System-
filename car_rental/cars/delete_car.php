<?php
require_once("../config/auth.php"); require_admin(); include("../config/db.php");
$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT status FROM cars WHERE car_id=?"); mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt);
$car = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if ($car && $car['status'] !== 'Rented') {
    $d = mysqli_prepare($conn, "DELETE FROM cars WHERE car_id=?"); mysqli_stmt_bind_param($d, "i", $id); mysqli_stmt_execute($d);
}
header("Location: view_car.php"); exit();