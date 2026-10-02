<?php
require_once("config/auth.php"); require_login();
include("config/db.php");
include("includes/functions.php");

$tab    = $_GET['tab'] ?? 'range';
$from   = $_GET['from'] ?? date('Y-m-01');
$to     = $_GET['to']   ?? date('Y-m-d');
$year   = (int)($_GET['year'] ?? date('Y'));

$summary = report_range($conn, $from, $to);
$perCar  = db_all($conn,
  "SELECT c.car_name, COUNT(r.rental_id) AS times,
          COALESCE(SUM(r.total_amount),0) AS rev
   FROM cars c LEFT JOIN rentals r ON r.car_id=c.car_id
     AND r.return_date BETWEEN ? AND ?
   GROUP BY c.car_id ORDER BY rev DESC", [$from,$to], 'ss');
$perCust = db_all($conn,
  "SELECT cu.name, COUNT(r.rental_id) AS times,
          COALESCE(SUM(r.total_amount),0) AS rev
   FROM customers cu LEFT JOIN rentals r ON r.customer_id=cu.customer_id
     AND r.return_date BETWEEN ? AND ?
   GROUP BY cu.customer_id ORDER BY rev DESC LIMIT 10", [$from,$to], 'ss');

$monthly   = monthly_data($conn, $year);
$yearly    = yearly_data($conn);
$quarterly = quarterly_data($conn, $year);

$mLabels = []; $mRev = [];
for ($i = 1; $i <= 12; $i++) {
    $mLabels[] = date('M', mktime(0,0,0,$i,1));
    $found = false;
    foreach ($monthly as $m) if ((int)$m['m'] === $i) {
        $mRev[] = (float)$m['revenue']; $found = true; break;
    }
    if (!$found) $mRev[] = 0;
}

include("includes/header.php");
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">
  <div class="card">
    <h2>📊 Reports</h2>

    <div class="tabs">
      <a class="tab <?= $tab==='range'?'active':'' ?>" href="?tab=range">📅 Date Range</a>
      <a class="tab <?= $tab==='monthly'?'active':'' ?>" href="?tab=monthly">📆 Monthly</a>
      <a class="tab <?= $tab==='quarterly'?'active':'' ?>" href="?tab=quarterly">🗓️ Quarterly</a>
      <a class="tab <?= $tab==='yearly'?'active':'' ?>" href="?tab=yearly">📈 Yearly</a>
    </div>

    <?php if ($tab === 'range'): ?>
      <form method="get" style="display:flex;gap:10px;flex-wrap:wrap">
        <input type="hidden" name="tab" value="range">
        <input type="date" name="from" value="<?= e($from) ?>" style="flex:1">
        <input type="date" name="to"   value="<?= e($to) ?>"   style="flex:1">
        <button class="btn">Generate</button>
      </form>
    <?php else: ?>
      <form method="get" style="display:flex;gap:10px">
        <input type="hidden" name="tab" value="<?= e($tab) ?>">
        <select name="year" style="flex:1">
          <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
            <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
        <button class="btn">Generate</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if ($tab === 'range'): ?>
    <div class="stats">
      <div class="stat"><h3><?= (int)$summary['total_rentals'] ?></h3><p>Rentals</p></div>
      <div class="stat"><h3 style="font-size:22px"><?= money($summary['revenue']) ?></h3><p>Revenue</p></div>
      <div class="stat"><h3 style="font-size:22px"><?= money($summary['avg_amount']) ?></h3><p>Avg/Rental</p></div>
      <div class="stat"><h3><?= (int)$summary['paid_count'] ?></h3><p>Paid</p></div>
      <div class="stat"><h3 style="color:#dc2626"><?= (int)$summary['unpaid_count'] ?></h3><p>Unpaid</p></div>
    </div>

    <div style="display:flex;gap:20px;flex-wrap:wrap">
      <div class="card" style="flex:1;min-width:350px">
        <h3>Per Car Performance</h3>
        <table>
          <tr><th>Car</th><th>Rentals</th><th>Revenue</th></tr>
          <?php foreach ($perCar as $r): ?>
            <tr><td><?= e($r['car_name']) ?></td>
                <td><?= (int)$r['times'] ?></td>
                <td><?= money($r['rev']) ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
      <div class="card" style="flex:1;min-width:350px">
        <h3>Top 10 Customers</h3>
        <table>
          <tr><th>Customer</th><th>Rentals</th><th>Revenue</th></tr>
          <?php foreach ($perCust as $r): ?>
            <tr><td><?= e($r['name']) ?></td>
                <td><?= (int)$r['times'] ?></td>
                <td><?= money($r['rev']) ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>

  <?php elseif ($tab === 'monthly'): ?>
    <div class="card">
      <h3>📆 Monthly Report — <?= $year ?></h3>
      <canvas id="monthlyChart" height="100"></canvas>
    </div>
    <div class="card">
      <table>
        <tr><th>Month</th><th>Rentals</th><th>Revenue</th></tr>
        <?php $totalM = 0; foreach ($monthly as $m): $totalM += $m['revenue']; ?>
          <tr>
            <td><?= date('F', mktime(0,0,0,(int)$m['m'],1)) ?></td>
            <td><?= (int)$m['rentals'] ?></td>
            <td><?= money($m['revenue']) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr style="background:#f9fafb;font-weight:bold">
          <td>TOTAL</td>
          <td><?= array_sum(array_column($monthly, 'rentals')) ?></td>
          <td><?= money($totalM) ?></td>
        </tr>
      </table>
    </div>

  <?php elseif ($tab === 'quarterly'): ?>
    <div class="card">
      <h3>🗓️ Quarterly Report — <?= $year ?></h3>
      <table>
        <tr><th>Quarter</th><th>Rentals</th><th>Revenue</th></tr>
        <?php
        $qs = [1=>'Q1 (Jan-Mar)',2=>'Q2 (Apr-Jun)',3=>'Q3 (Jul-Sep)',4=>'Q4 (Oct-Dec)'];
        $qData = [];
        foreach ($quarterly as $q) $qData[(int)$q['q']] = $q;
        foreach ($qs as $n => $label):
          $d = $qData[$n] ?? ['rentals'=>0,'revenue'=>0];
        ?>
          <tr>
            <td><?= $label ?></td>
            <td><?= (int)$d['rentals'] ?></td>
            <td><?= money($d['revenue']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>

  <?php elseif ($tab === 'yearly'): ?>
    <div class="card">
      <h3>📈 Yearly Report</h3>
      <canvas id="yearlyChart" height="100"></canvas>
    </div>
    <div class="card">
      <table>
        <tr><th>Year</th><th>Rentals</th><th>Revenue</th></tr>
        <?php foreach ($yearly as $y): ?>
          <tr>
            <td><?= (int)$y['y'] ?></td>
            <td><?= (int)$y['rentals'] ?></td>
            <td><?= money($y['revenue']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php if ($tab === 'monthly'): ?>
<script>
new Chart(document.getElementById('monthlyChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($mLabels) ?>,
    datasets: [
      { label: 'Revenue (Rs.)', data: <?= json_encode($mRev) ?>,
        backgroundColor: '#2563eb' }
    ]
  },
  options: { responsive: true }
});
</script>
<?php elseif ($tab === 'yearly'): ?>
<script>
new Chart(document.getElementById('yearlyChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode(array_column($yearly, 'y')) ?>,
    datasets: [{
      label: 'Revenue (Rs.)',
      data: <?= json_encode(array_map('floatval', array_column($yearly, 'revenue'))) ?>,
      borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.15)',
      tension: .3, fill: true
    }]
  },
  options: { responsive: true }
});
</script>
<?php endif; ?>