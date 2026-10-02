<?php
require_once("../config/auth.php");
require_login();
include("../config/db.php");
include("../includes/functions.php");

/* CSV Export */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = db_all($conn, "SELECT customer_id, name, address, phone, email, license_no FROM customers ORDER BY customer_id DESC");
    $data = array_map(fn($r) => array_values($r), $rows);
    export_csv('customers_' . date('Y-m-d') . '.csv',
        ['ID','Name','Address','Phone','Email','License'], $data);
}

/* Filters */
$search = trim($_GET['q'] ?? '');
$sort   = $_GET['sort'] ?? 'customer_id';
$dir    = ($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
$allowedSort = ['customer_id','name','phone'];
if (!in_array($sort, $allowedSort)) $sort = 'customer_id';

$where = ['1=1']; $params = []; $types = '';
if ($search !== '') {
    $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ? OR license_no LIKE ?)";
    $kw = "%$search%";
    $params = [$kw,$kw,$kw,$kw]; $types = 'ssss';
}
$whereSql = implode(' AND ', $where);

$data = paginate($conn,
    "SELECT c.*,
        (SELECT COUNT(*) FROM rentals r WHERE r.customer_id=c.customer_id) AS total_rentals,
        (SELECT COALESCE(SUM(total_amount),0) FROM rentals r WHERE r.customer_id=c.customer_id) AS total_spent
     FROM customers c WHERE $whereSql ORDER BY $sort $dir",
    "SELECT COUNT(*) FROM customers WHERE $whereSql",
    $params, $types, 10);

include("../includes/header.php");
?>
<div class="container">
  <?php show_flash(); ?>

  <div class="card">
    <h2>👥 Customers
      <span style="float:right">
        <a class="btn" href="add_customer.php">+ Add</a>
        <a class="btn" style="background:#059669" href="?export=csv">📥 CSV</a>
        <a class="btn" style="background:#6b7280" href="javascript:window.print()">🖨️ Print</a>
      </span>
    </h2>

    <form method="get" style="display:flex;gap:10px;margin-bottom:15px">
      <input name="q" value="<?= e($search) ?>" placeholder="🔍 Search name/phone/email/license"
             style="flex:3">
      <button class="btn">Filter</button>
      <a class="btn" style="background:#6b7280" href="view_customer.php">Reset</a>
    </form>

    <p style="color:#6b7280;font-size:13px">
      <?= count($data['rows']) ?> of <?= $data['total'] ?> customers
    </p>

    <table>
      <tr>
        <th><?= sort_link('customer_id','ID',$sort,$dir) ?></th>
        <th><?= sort_link('name','Name',$sort,$dir) ?></th>
        <th><?= sort_link('phone','Phone',$sort,$dir) ?></th>
        <th>Email</th>
        <th>License</th>
        <th>Rentals</th>
        <th>Total Spent</th>
        <th>Action</th>
      </tr>
      <?php if (empty($data['rows'])): ?>
        <tr><td colspan="8" style="text-align:center;color:#999">No customers</td></tr>
      <?php else: foreach ($data['rows'] as $r): ?>
        <tr>
          <td><?= (int)$r['customer_id'] ?></td>
          <td><?= e($r['name']) ?></td>
          <td><?= e($r['phone']) ?></td>
          <td><?= e($r['email']) ?></td>
          <td><code><?= e($r['license_no']) ?></code></td>
          <td><?= (int)$r['total_rentals'] ?></td>
          <td><?= money($r['total_spent']) ?></td>
          <td>
            <a class="btn" href="view_customer_detail.php?id=<?= (int)$r['customer_id'] ?>">👁️</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </table>

    <?php pagination_links($data, build_qs(['p'=>null])); ?>
  </div>
</div>