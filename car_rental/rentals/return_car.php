<?php
require_once("../config/auth.php");
require_login();
include("../config/db.php");
include("../includes/functions.php");

$msg = "";
if (isset($_POST['return'])) {
    $rid = (int)$_POST['rid'];
    $row = db_row($conn,
      "SELECT r.car_id, r.rental_date, c.rental_price
       FROM rentals r JOIN cars c ON c.car_id=r.car_id
       WHERE r.rental_id=? AND r.rental_status='Rented'", [$rid], 'i');

    if ($row) {
        $days  = days_between($row['rental_date']);
        $total = $days * $row['rental_price'];

        mysqli_begin_transaction($conn);
        try {
            $s1 = mysqli_prepare($conn,
              "UPDATE rentals SET rental_status='Returned', return_date=CURDATE(), total_amount=?
               WHERE rental_id=?");

            mysqli_stmt_bind_param($s1, "di", $total, $rid);
            mysqli_stmt_execute($s1);

            $s2 = mysqli_prepare($conn, "UPDATE cars SET status='Available' WHERE car_id=?");
            mysqli_stmt_bind_param($s2, "i", $row['car_id']);
            mysqli_stmt_execute($s2);

            mysqli_commit($conn);
            log_activity($conn, 'return_car', "Rental #$rid returned - " . money($total));
            set_flash('success', "Car returned! Total: " . money($total));
            redirect("rentals/view_rentals.php");
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            $msg = "Return failed!";
        }
    } else $msg = "Rental not found or already returned.";
}

/* Active rentals */
$active = db_all($conn,
  "SELECT r.rental_id, r.rental_date, c.car_name, c.registration_no, c.rental_price,
          cu.name AS cust_name,
          DATEDIFF(CURDATE(), r.rental_date) AS days_out
   FROM rentals r
   JOIN cars c ON c.car_id=r.car_id
   JOIN customers cu ON cu.customer_id=r.customer_id
   WHERE r.rental_status='Rented' ORDER BY r.rental_date");

include("../includes/header.php");
?>
<div class="container">
  <?php show_flash(); ?>

  <div class="card" style="max-width:600px">
    <h2>↩️ Return Car</h2>
    <?php if ($msg): ?><p style="color:#dc2626"><?= e($msg) ?></p><?php endif; ?>

    <?php if (empty($active)): ?>
      <p style="color:#6b7280">No active rentals. 🎉</p>
    <?php else: ?>
      <form method="post">
        <label>Select Rental</label>
        <select name="rid" required>
          <?php foreach ($active as $r):
            $od = $r['days_out'] > 7 ? ' ⚠️ OVERDUE' : '';
            $est = days_between($r['rental_date']) * $r['rental_price'];
          ?>
            <option value="<?= (int)$r['rental_id'] ?>">
              #<?= (int)$r['rental_id'] ?> — <?= e($r['car_name']) ?>
              (<?= e($r['registration_no']) ?>) → <?= e($r['cust_name']) ?>
              [<?= (int)$r['days_out'] ?> days, ~<?= money($est) ?>]<?= $od ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="btn" name="return" style="width:100%">Return Car</button>
      </form>

      <h3 style="margin-top:20px">Active Rentals (<?= count($active) ?>)</h3>
      <table>
        <tr><th>#</th><th>Car</th><th>Customer</th><th>Days</th><th>Est. Total</th></tr>
        <?php foreach ($active as $r):
          $od = $r['days_out'] > 7;
        ?>
          <tr style="<?= $od ? 'background:#fef2f2' : '' ?>">
            <td>#<?= (int)$r['rental_id'] ?></td>
            <td><?= e($r['car_name']) ?></td>
            <td><?= e($r['cust_name']) ?></td>
            <td><?= (int)$r['days_out'] ?><?= $od ? ' ⚠️' : '' ?></td>
            <td><?= money(days_between($r['rental_date']) * $r['rental_price']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>