<?php
require_once("../config/auth.php"); require_login();
include("../config/db.php");
include("../includes/functions.php");

$id = (int)($_GET['id'] ?? 0);
$r = db_row($conn,
  "SELECT r.*, c.car_name, c.brand, c.registration_no, c.rental_price,
          cu.name AS cust_name, cu.address, cu.phone, cu.email, cu.license_no
   FROM rentals r
   JOIN cars c ON c.car_id=r.car_id
   JOIN customers cu ON cu.customer_id=r.customer_id
   WHERE r.rental_id=?", [$id], 'i');

if (!$r) die("Invoice not found");

$days  = days_between($r['rental_date'], $r['return_date']);
$subtotal = $days * $r['rental_price'];
$total = $r['total_amount'] > 0 ? $r['total_amount'] : $subtotal;

include("../includes/header.php");
?>
<div class="container">
  <div class="card" style="max-width:750px;margin:20px auto">

    <div style="display:flex;justify-content:space-between;border-bottom:3px solid #2563eb;padding-bottom:15px">
      <div>
        <h1 style="margin:0;color:#2563eb">🚗 Car Rental System</h1>
        <small>Colombo, Sri Lanka · +94 11 234 5678</small><br>
        <small>info@carrental.lk</small>
      </div>
      <div style="text-align:right">
        <h2 style="margin:0">INVOICE</h2>
        <div style="font-family:monospace"><?= invoice_no($id) ?></div>
        <small><?= fdate($r['return_date'] ?: date('Y-m-d')) ?></small>
      </div>
    </div>

    <div style="display:flex;justify-content:space-between;margin-top:20px">
      <div style="flex:1">
        <b style="color:#2563eb">BILL TO:</b><br>
        <b><?= e($r['cust_name']) ?></b><br>
        <?= e($r['address']) ?><br>
        📞 <?= e($r['phone']) ?><br>
        ✉️ <?= e($r['email']) ?><br>
        License: <?= e($r['license_no']) ?>
      </div>
      <div style="flex:1;text-align:right">
        <b style="color:#2563eb">VEHICLE:</b><br>
        <b><?= e($r['car_name']) ?></b><br>
        <?= e($r['brand']) ?><br>
        Reg: <code><?= e($r['registration_no']) ?></code><br>
        Rate: <?= money($r['rental_price']) ?>/day
      </div>
    </div>

    <table style="margin-top:25px">
      <tr>
        <th>Description</th>
        <th style="text-align:center">Days</th>
        <th style="text-align:right">Rate</th>
        <th style="text-align:right">Amount</th>
      </tr>
      <tr>
        <td>
          <b>Car Rental — <?= e($r['car_name']) ?></b><br>
          <small>From <?= fdate($r['rental_date']) ?> to <?= fdate($r['return_date']) ?></small>
        </td>
        <td style="text-align:center"><?= $days ?></td>
        <td style="text-align:right"><?= money($r['rental_price']) ?></td>
        <td style="text-align:right"><?= money($subtotal) ?></td>
      </tr>
      <tr>
        <td colspan="3" style="text-align:right"><b>TOTAL</b></td>
        <td style="text-align:right;font-size:18px;color:#2563eb">
          <b><?= money($total) ?></b>
        </td>
      </tr>
    </table>

    <div style="margin-top:20px;padding:15px;border-radius:6px;
                background:<?= $r['payment_status']==='Paid' ? '#dcfce7' : '#fee2e2' ?>;
                color:<?= $r['payment_status']==='Paid' ? '#166534' : '#991b1b' ?>">
      <b>Payment Status: <?= e($r['payment_status']) ?></b>
      <?php if ($r['payment_status'] === 'Paid'): ?>
        <br><small>Paid on <?= fdate($r['payment_date']) ?>
              via <?= e($r['payment_method']) ?></small>
      <?php endif; ?>
    </div>

    <p style="margin-top:30px;text-align:center;color:#6b7280">
      Thank you for your business! 🙏
    </p>
  </div>

  <div class="no-print" style="text-align:center;margin-top:20px">
    <button class="btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    <a class="btn" style="background:#6b7280" href="view_rentals.php">← Back</a>
    <?php if ($r['payment_status'] === 'Unpaid'): ?>
      <a class="btn btn-success" href="view_rentals.php?mark_paid=<?= (int)$r['rental_id'] ?>"
         onclick="return confirm('Mark as PAID?')">💰 Mark Paid</a>
    <?php endif; ?>
  </div>
</div>

<style>
@media print {
  .nav, .no-print { display: none !important; }
  body { background: #fff !important; color: #000 !important; }
  .card { box-shadow: none !important; margin: 0 !important; }
}
</style>