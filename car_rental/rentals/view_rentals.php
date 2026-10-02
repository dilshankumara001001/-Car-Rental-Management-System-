<?php
require_once("../config/auth.php"); require_login();
include("../config/db.php");
include("../includes/functions.php");

/* Mark Payment */
if (isset($_GET['mark_paid'])) {
    $rid = (int)$_GET['mark_paid'];
    mark_payment($conn, $rid, 'Paid', $_GET['method'] ?? 'Cash');
    set_flash('success', "Rental #$rid marked as PAID");
    redirect('rentals/view_rentals.php');
}
if (isset($_GET['mark_unpaid'])) {
    $rid = (int)$_GET['mark_unpaid'];
    mysqli_query($conn, "UPDATE rentals SET payment_status='Unpaid',
                         payment_date=NULL, paid_amount=0
                         WHERE rental_id=$rid");
    set_flash('warning', "Rental #$rid marked as UNPAID");
    redirect('rentals/view_rentals.php');
}

/* CSV */
if (isset($_GET['export'])) {
    $rows = db_all($conn,
      "SELECT r.rental_id, c.car_name, cu.name, r.rental_date, r.return_date,
              r.total_amount, r.rental_status, r.payment_status
       FROM rentals r JOIN cars c ON c.car_id=r.car_id
       JOIN customers cu ON cu.customer_id=r.customer_id
       ORDER BY r.rental_id DESC");
    export_csv('rentals_' . date('Y-m-d') . '.csv',
      ['ID','Car','Customer','Rented','Returned','Total','Rental','Payment'],
      array_map(fn($r) => array_values($r), $rows));
}

$search   = trim($_GET['q'] ?? '');
$status   = $_GET['status'] ?? '';
$payment  = $_GET['payment'] ?? '';
$sort     = $_GET['sort'] ?? 'rental_id';
$dir      = ($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
$allowed  = ['rental_id','rental_date','total_amount'];
if (!in_array($sort, $allowed)) $sort = 'rental_id';

$where = ['1=1']; $params = []; $types = '';
if ($search !== '') {
    $where[] = "(c.car_name LIKE ? OR cu.name LIKE ?)";
    $kw = "%$search%"; $params = [$kw, $kw]; $types .= 'ss';
}
if ($status !== '')  { $where[] = "r.rental_status=?";  $params[] = $status;  $types .= 's'; }
if ($payment !== '') { $where[] = "r.payment_status=?"; $params[] = $payment; $types .= 's'; }
$whereSql = implode(' AND ', $where);

$data = paginate($conn,
  "SELECT r.*, c.car_name, cu.name AS cust_name
   FROM rentals r
   JOIN cars c ON c.car_id=r.car_id
   JOIN customers cu ON cu.customer_id=r.customer_id
   WHERE $whereSql ORDER BY r.$sort $dir",
  "SELECT COUNT(*) FROM rentals r
   JOIN cars c ON c.car_id=r.car_id
   JOIN customers cu ON cu.customer_id=r.customer_id
   WHERE $whereSql",
  $params, $types, 10);

include("../includes/header.php");
?>
<div class="container">
  <?php show_flash(); ?>

  <div class="card">
    <h2>📋 Rentals
      <span style="float:right">
        <a class="btn btn-success" href="?export=csv">📥 CSV</a>
        <a class="btn" style="background:#6b7280" href="javascript:window.print()">🖨️ Print</a>
      </span>
    </h2>

    <form method="get" style="display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap">
      <input name="q" value="<?= e($search) ?>" placeholder="🔍 Search car/customer" style="flex:2;min-width:200px">
      <select name="status">
        <option value="">All Rental</option>
        <option value="Rented"   <?= $status==='Rented'?'selected':'' ?>>Rented</option>
        <option value="Returned" <?= $status==='Returned'?'selected':'' ?>>Returned</option>
      </select>
      <select name="payment">
        <option value="">All Payment</option>
        <option value="Paid"    <?= $payment==='Paid'?'selected':'' ?>>Paid</option>
        <option value="Unpaid"  <?= $payment==='Unpaid'?'selected':'' ?>>Unpaid</option>
      </select>
      <button class="btn">Filter</button>
      <a class="btn" style="background:#6b7280" href="view_rentals.php">Reset</a>
    </form>

    <table>
      <tr>
        <th>#</th><th>Invoice</th><th>Car</th><th>Customer</th>
        <th>Rented</th><th>Returned</th>
        <th>Total</th><th>Rental</th><th>Payment</th><th>Action</th>
      </tr>
      <?php if (empty($data['rows'])): ?>
        <tr><td colspan="10" style="text-align:center;color:#999">No rentals</td></tr>
      <?php else: foreach ($data['rows'] as $r): ?>
        <tr>
          <td>#<?= (int)$r['rental_id'] ?></td>
          <td><code><?= invoice_no($r['rental_id']) ?></code></td>
          <td><?= e($r['car_name']) ?></td>
          <td><?= e($r['cust_name']) ?></td>
          <td><?= fdate($r['rental_date']) ?></td>
          <td><?= fdate($r['return_date']) ?></td>
          <td><?= $r['total_amount'] > 0 ? money($r['total_amount']) : '—' ?></td>
          <td><?= badge($r['rental_status']) ?></td>
          <td><?= payment_badge($r['payment_status']) ?></td>
          <td>
            <a class="btn btn-sm" href="invoice.php?id=<?= (int)$r['rental_id'] ?>">🧾</a>
            <?php if ($r['rental_status'] === 'Returned'): ?>
              <?php if ($r['payment_status'] === 'Unpaid'): ?>
                <a class="btn btn-sm btn-success"
                   href="?mark_paid=<?= (int)$r['rental_id'] ?>"
                   onclick="return confirm('Mark as PAID?')">💰</a>
              <?php else: ?>
                <a class="btn btn-sm btn-warning"
                   href="?mark_unpaid=<?= (int)$r['rental_id'] ?>"
                   onclick="return confirm('Mark as UNPAID?')">↺</a>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </table>

    <?php pagination_links($data, build_qs(['p'=>null])); ?>
  </div>
</div>