<?php
require_once("config/auth.php"); require_login();
include("config/db.php");
include("includes/functions.php");

$totalCars     = (int)db_value($conn, "SELECT COUNT(*) FROM cars");
$availCars     = (int)db_value($conn, "SELECT COUNT(*) FROM cars WHERE status='Available'");
$rentedCars    = (int)db_value($conn, "SELECT COUNT(*) FROM cars WHERE status='Rented'");
$totalCust     = (int)db_value($conn, "SELECT COUNT(*) FROM customers");
$activeRentals = (int)db_value($conn, "SELECT COUNT(*) FROM rentals WHERE rental_status='Rented'");

$monthRev  = (float)db_value($conn, "SELECT COALESCE(SUM(total_amount),0) FROM rentals
                                       WHERE MONTH(return_date)=MONTH(CURDATE())
                                       AND YEAR(return_date)=YEAR(CURDATE())");
$allRev    = (float)db_value($conn, "SELECT COALESCE(SUM(total_amount),0) FROM rentals
                                       WHERE rental_status='Returned'");
$unpaid    = (float)db_value($conn, "SELECT COALESCE(SUM(total_amount),0) FROM rentals
                                       WHERE payment_status='Unpaid'
                                       AND rental_status='Returned'");

$chartRows = db_all($conn,
  "SELECT DATE_FORMAT(return_date,'%b %Y') AS lbl,
          DATE_FORMAT(return_date,'%Y-%m') AS ym,
          SUM(total_amount) AS rev
   FROM rentals WHERE rental_status='Returned'
     AND return_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
   GROUP BY ym ORDER BY ym");

$chartLabels = array_column($chartRows, 'lbl');
$chartRev    = array_map('floatval', array_column($chartRows, 'rev'));

$pieAvail  = (int)db_value($conn, "SELECT COUNT(*) FROM cars WHERE status='Available'");
$pieRent   = (int)db_value($conn, "SELECT COUNT(*) FROM cars WHERE status='Rented'");
$pieMaint  = (int)db_value($conn, "SELECT COUNT(*) FROM cars WHERE status='Maintenance'");

$topCars = db_all($conn,
  "SELECT c.car_name, COUNT(r.rental_id) AS times
   FROM cars c LEFT JOIN rentals r ON r.car_id=c.car_id
   GROUP BY c.car_id ORDER BY times DESC LIMIT 5");

$topCarsLabels = array_column($topCars, 'car_name');
$topCarsData   = array_map('intval', array_column($topCars, 'times'));

include("includes/header.php");
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">

  <!-- Hero Banner -->
  <div class="card" style="background:linear-gradient(135deg,#6366f1,#8b5cf6 50%,#ec4899);
                            color:#fff;border:none;overflow:hidden;position:relative">
    <div style="position:relative;z-index:2">
      <h1 style="color:#fff;font-size:30px">👋 Welcome back, <?= e($_SESSION['username']) ?>!</h1>
      <p style="opacity:.9;margin-top:6px">
        <?= date('l, d F Y') ?> · Here's what's happening with your fleet today
      </p>
    </div>
    <div style="position:absolute;right:-20px;top:-20px;font-size:180px;opacity:.1">🚗</div>
  </div>

  <!-- Stats Row 1 -->
  <div class="stats">
    <div class="stat">
      <h3><?= $totalCars ?></h3>
      <p>Total Cars</p>
    </div>
    <div class="stat success">
      <h3><?= $availCars ?></h3>
      <p>Available</p>
    </div>
    <div class="stat danger">
      <h3><?= $rentedCars ?></h3>
      <p>Rented</p>
    </div>
    <div class="stat info">
      <h3><?= $totalCust ?></h3>
      <p>Customers</p>
    </div>
  </div>

  <!-- Stats Row 2 — Revenue -->
  <div class="stats">
    <div class="stat info">
      <h3 style="font-size:22px"><?= money($monthRev) ?></h3>
      <p>This Month</p>
    </div>
    <div class="stat success">
      <h3 style="font-size:22px"><?= money($allRev) ?></h3>
      <p>All Revenue</p>
    </div>
    <div class="stat danger">
      <h3 style="font-size:22px"><?= money($unpaid) ?></h3>
      <p>Unpaid</p>
    </div>
    <div class="stat">
      <h3><?= $activeRentals ?></h3>
      <p>Active Rentals</p>
    </div>
  </div>

  <!-- Charts -->
  <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-top:22px">
    <div class="card" style="margin-top:0">
      <h3>📈 Revenue Trend (Last 6 Months)</h3>
      <canvas id="revChart" height="110"></canvas>
    </div>
    <div class="card" style="margin-top:0">
      <h3>🚗 Fleet Status</h3>
      <canvas id="pieChart" height="180"></canvas>
    </div>
  </div>

  <div class="card">
    <h3>🔥 Top 5 Most Rented Cars</h3>
    <canvas id="topCarsChart" height="80"></canvas>
  </div>

  <!-- Quick Actions -->
  <div class="card">
    <h3>⚡ Quick Actions</h3>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
      <a class="btn" href="<?= BASE_URL ?>cars/add_car.php">➕ Add Car</a>
      <a class="btn" href="<?= BASE_URL ?>customers/add_customer.php">➕ Add Customer</a>
      <a class="btn btn-success" href="<?= BASE_URL ?>rentals/rent_car.php">🔑 Rent Car</a>
      <a class="btn btn-warning" href="<?= BASE_URL ?>rentals/return_car.php">↩️ Return Car</a>
      <a class="btn" href="<?= BASE_URL ?>reports.php">📊 View Reports</a>
    </div>
  </div>
</div>

<script>
const chartDefaults = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      labels: { color: document.body.classList.contains('dark') ? '#e2e8f0' : '#0f172a' }
    }
  },
  scales: {
    x: { grid: { display: false }, ticks: { color: document.body.classList.contains('dark') ? '#94a3b8' : '#64748b' } },
    y: { grid: { color: 'rgba(148,163,184,.15)' }, ticks: { color: document.body.classList.contains('dark') ? '#94a3b8' : '#64748b' } }
  }
};

/* Revenue Line Chart */
const revCtx = document.getElementById('revChart').getContext('2d');
const gradient = revCtx.createLinearGradient(0,0,0,300);
gradient.addColorStop(0, 'rgba(99,102,241,.4)');
gradient.addColorStop(1, 'rgba(99,102,241,0)');

new Chart(revCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Revenue (Rs.)',
      data: <?= json_encode($chartRev) ?>,
      borderColor: '#6366f1',
      backgroundColor: gradient,
      borderWidth: 3,
      tension: .4,
      fill: true,
      pointBackgroundColor: '#6366f1',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 5,
      pointHoverRadius: 8
    }]
  },
  options: {
    ...chartDefaults,
    plugins: { legend: { display: false } }
  }
});

/* Fleet Doughnut */
new Chart(document.getElementById('pieChart'), {
  type: 'doughnut',
  data: {
    labels: ['Available','Rented','Maintenance'],
    datasets: [{
      data: [<?= $pieAvail ?>, <?= $pieRent ?>, <?= $pieMaint ?>],
      backgroundColor: ['#10b981','#f43f5e','#f59e0b'],
      borderWidth: 0,
      hoverOffset: 8
    }]
  },
  options: {
    responsive: true,
    cutout: '65%',
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          padding: 15,
          usePointStyle: true,
          color: document.body.classList.contains('dark') ? '#e2e8f0' : '#0f172a'
        }
      }
    }
  }
});

/* Top Cars Bar */
new Chart(document.getElementById('topCarsChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($topCarsLabels) ?>,
    datasets: [{
      label: 'Rentals',
      data: <?= json_encode($topCarsData) ?>,
      backgroundColor: [
        'rgba(99,102,241,.85)',
        'rgba(139,92,246,.85)',
        'rgba(236,72,153,.85)',
        'rgba(6,182,212,.85)',
        'rgba(16,185,129,.85)'
      ],
      borderRadius: 10,
      borderSkipped: false,
      barThickness: 40
    }]
  },
  options: {
    ...chartDefaults,
    plugins: { legend: { display: false } },
    indexAxis: 'y'
  }
});
</script>