<?php
require_once("../config/auth.php");
require_login();
include("../config/db.php");
include("../includes/functions.php");

/* ─── CSV Export ─── */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = db_all($conn, "SELECT car_id, car_name, brand, model, year,
                                 registration_no, rental_price, status
                          FROM cars ORDER BY car_id DESC");
    $data = array_map(fn($r) => [
        $r['car_id'], $r['car_name'], $r['brand'], $r['model'], $r['year'],
        $r['registration_no'], $r['rental_price'], $r['status']
    ], $rows);
    export_csv('cars_' . date('Y-m-d') . '.csv',
        ['ID','Name','Brand','Model','Year','Reg No','Price','Status'], $data);
}

/* ─── Filters ─── */
$search  = trim($_GET['q']      ?? '');
$status  = trim($_GET['status'] ?? '');
$brand   = trim($_GET['brand']  ?? '');
$sort    = $_GET['sort'] ?? 'car_id';
$dir     = ($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

$allowedSort = ['car_id','car_name','brand','year','rental_price','status'];
if (!in_array($sort, $allowedSort)) $sort = 'car_id';

$where = ['1=1'];
$params = []; $types = '';

if ($search !== '') {
    $where[] = "(car_name LIKE ? OR brand LIKE ? OR registration_no LIKE ?)";
    $kw = "%$search%";
    $params = array_merge($params, [$kw, $kw, $kw]);
    $types .= 'sss';
}
if ($status !== '') { $where[] = "status = ?"; $params[] = $status; $types .= 's'; }
if ($brand  !== '') { $where[] = "brand  = ?"; $params[] = $brand;  $types .= 's'; }
$whereSql = implode(' AND ', $where);

/* ─── Pagination ─── */
$data = paginate(
    $conn,
    "SELECT * FROM cars WHERE $whereSql ORDER BY $sort $dir",
    "SELECT COUNT(*) FROM cars WHERE $whereSql",
    $params, $types, 10
);

/* ─── Get brand list for filter ─── */
$brands = db_all($conn, "SELECT DISTINCT brand FROM cars WHERE brand IS NOT NULL ORDER BY brand");

include("../includes/header.php");
?>
<div class="container">
  <?php show_flash(); ?>

  <div class="card">
    <h2>🚗 Cars
      <span style="float:right">
        <?php if (can('*')): ?>
          <a class="btn" href="add_car.php">+ Add Car</a>
        <?php endif; ?>
        <a class="btn" style="background:#059669" href="?export=csv">📥 CSV</a>
        <a class="btn" style="background:#6b7280" href="javascript:window.print()">🖨️ Print</a>
      </span>
    </h2>

    <!-- Search + Filters -->
    <form method="get" style="display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap">
      <input name="q" value="<?= e($search) ?>" placeholder="🔍 Search name/brand/reg..."
             style="flex:2;min-width:200px">
      <select name="status" style="flex:1;min-width:120px">
        <option value="">All Status</option>
        <?php foreach (['Available','Rented','Maintenance'] as $s): ?>
          <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
      <select name="brand" style="flex:1;min-width:120px">
        <option value="">All Brands</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= e($b['brand']) ?>" <?= $brand===$b['brand']?'selected':'' ?>>
            <?= e($b['brand']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn" style="flex:0">Filter</button>
      <a class="btn" style="background:#6b7280;flex:0" href="view_car.php">Reset</a>
    </form>

    <p style="color:#6b7280;font-size:13px">
      Showing <?= count($data['rows']) ?> of <?= $data['total'] ?> cars
    </p>

    <table>
      <tr>
        <th><?= sort_link('car_id','ID',$sort,$dir) ?></th>
        <th><?= sort_link('car_name','Name',$sort,$dir) ?></th>
        <th><?= sort_link('brand','Brand',$sort,$dir) ?></th>
        <th>Model</th>
        <th><?= sort_link('year','Year',$sort,$dir) ?></th>
        <th>Reg No</th>
        <th><?= sort_link('rental_price','Price/Day',$sort,$dir) ?></th>
        <th><?= sort_link('status','Status',$sort,$dir) ?></th>
        <th>Action</th>
      </tr>
      <?php if (empty($data['rows'])): ?>
        <tr><td colspan="9" style="text-align:center;color:#999">No cars found</td></tr>
      <?php else: foreach ($data['rows'] as $r): ?>
        <tr>
          <td><?= (int)$r['car_id'] ?></td>
          <td><?= e($r['car_name']) ?></td>
          <td><?= e($r['brand']) ?></td>
          <td><?= e($r['model']) ?></td>
          <td><?= (int)$r['year'] ?></td>
          <td><code><?= e($r['registration_no']) ?></code></td>
          <td><?= money($r['rental_price']) ?></td>
          <td><?= badge($r['status']) ?></td>
          <td>
            <?php if (can('*')): ?>
              <a class="btn" href="edit_car.php?id=<?= (int)$r['car_id'] ?>">✏️</a>
              <a class="btn btn-danger"
                 href="delete_car.php?id=<?= (int)$r['car_id'] ?>"
                 onclick="return confirm('Delete this car?')">🗑️</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </table>

    <?php pagination_links($data, build_qs(['p'=>null])); ?>
  </div>
</div>