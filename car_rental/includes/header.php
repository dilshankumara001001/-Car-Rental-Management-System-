<?php
require_once(__DIR__ . '/../config/auth.php');
require_once(__DIR__ . '/functions.php');

if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (isset($conn) && ($_SESSION['role'] ?? '') === 'admin') {
    check_overdue_rentals($conn, 7);
}

$theme = 'light';
if (isset($conn) && isset($_SESSION['user_id'])) {
    $theme = user_theme($conn);
}

$notif_count = 0;
if (isset($conn)) {
    $notif_count = get_unread_count($conn, $_SESSION['user_id'] ?? null);
}

/* Flash → Toast */
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>🚗 Car Rental System</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body class="<?= $theme === 'dark' ? 'dark' : '' ?>">

<!-- ═══════════ NAV BAR ═══════════ -->
<nav class="nav">
  <div class="nav-inner">
    <span class="nav-brand">🚗 CarRental</span>

    <a href="<?= BASE_URL ?>index.php">Dashboard</a>
    <a href="<?= BASE_URL ?>cars/view_car.php">Cars</a>
    <a href="<?= BASE_URL ?>customers/view_customer.php">Customers</a>
    <a href="<?= BASE_URL ?>rentals/view_rentals.php">Rentals</a>
    <a href="<?= BASE_URL ?>rentals/rent_car.php">Rent</a>
    <a href="<?= BASE_URL ?>rentals/return_car.php">Return</a>
    <a href="<?= BASE_URL ?>reports.php">Reports</a>
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
      <a href="<?= BASE_URL ?>users/manage_users.php">Users</a>
    <?php endif; ?>

    <div class="nav-right">
      <span class="nav-user">
        👤 <?= e($_SESSION['username'] ?? 'Guest') ?>
      </span>

      <a href="#" class="notif-bell" onclick="toggleNotif(event)" title="Notifications">
        🔔
        <?php if ($notif_count > 0): ?>
          <span class="notif-badge"><?= $notif_count ?></span>
        <?php endif; ?>
      </a>

      <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">
        <?= $theme === 'dark' ? '☀️' : '🌙' ?>
      </button>

      <a href="<?= BASE_URL ?>auth/logout.php" title="Logout">🚪</a>
    </div>
  </div>
</nav>

<!-- ═══════════ NOTIFICATION PANEL ═══════════ -->
<div class="notif-panel" id="notifPanel">
  <div class="notif-head">
    <b>🔔 Notifications</b>
    <a href="#" onclick="markAllRead(event)"
       style="color:#fff;font-size:12px;text-decoration:none">Mark all read</a>
  </div>
  <div id="notifList">
    <div class="notif-item text-center" style="color:#999">
      <div class="spinner" style="margin:auto"></div>
    </div>
  </div>
</div>

<!-- ═══════════ TOAST CONTAINER ═══════════ -->
<div class="toast-container" id="toastContainer"></div>

<script>
/* ─────────── Toast System ─────────── */
function showToast(message, type = 'info', duration = 3500) {
  const c = document.getElementById('toastContainer');
  const icons = { success:'✅', error:'❌', warning:'⚠️', info:'ℹ️' };
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.innerHTML = `<span style="font-size:18px">${icons[type] || 'ℹ️'}</span>
                 <span>${message}</span>`;
  c.appendChild(t);
  setTimeout(() => {
    t.classList.add('removing');
    setTimeout(() => t.remove(), 300);
  }, duration);
}

/* Flash → Toast on load */
<?php if ($flash): ?>
document.addEventListener('DOMContentLoaded', () => {
  showToast(<?= json_encode($flash['msg']) ?>, <?= json_encode($flash['type']) ?>);
});
<?php endif; ?>

/* ─────────── Notification Panel ─────────── */
function toggleNotif(e) {
  e.preventDefault();
  const p = document.getElementById('notifPanel');
  p.classList.toggle('open');
  if (p.classList.contains('open')) loadNotifs();
}

function loadNotifs() {
  fetch('<?= BASE_URL ?>notifications_api.php?action=list')
    .then(r => r.json())
    .then(data => {
      const list = document.getElementById('notifList');
      if (!data.length) {
        list.innerHTML = `<div class="notif-item text-center" style="color:#94a3b8;padding:30px">
                            <div style="font-size:32px;opacity:.4">🔕</div>
                            <div style="margin-top:8px">No notifications</div>
                          </div>`;
        return;
      }
      list.innerHTML = data.map(n => `
        <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}">
          <b>${escapeHtml(n.title)}</b>
          <div style="font-size:13px;color:var(--text-muted)">${escapeHtml(n.message)}</div>
          <small>🕐 ${n.created_at}</small>
        </div>
      `).join('');
    })
    .catch(() => {
      document.getElementById('notifList').innerHTML =
        '<div class="notif-item text-center" style="color:#dc2626">Failed to load</div>';
    });
}

function markAllRead(e) {
  e.preventDefault();
  fetch('<?= BASE_URL ?>notifications_api.php?action=read_all')
    .then(() => {
      loadNotifs();
      const badge = document.querySelector('.notif-badge');
      if (badge) badge.remove();
      showToast('All notifications marked as read', 'success');
    });
}

function escapeHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

/* ─────────── Theme Toggle ─────────── */
function toggleTheme() {
  document.body.style.transition = 'all .4s';
  fetch('<?= BASE_URL ?>notifications_api.php?action=toggle_theme')
    .then(r => r.json())
    .then(data => {
      document.body.classList.toggle('dark', data.theme === 'dark');
      const btn = document.querySelector('.theme-toggle');
      btn.textContent = data.theme === 'dark' ? '☀️' : '🌙';
      showToast(data.theme === 'dark' ? '🌙 Dark mode enabled' : '☀️ Light mode enabled', 'info', 2000);
    });
}

/* Close notif panel when clicking outside */
document.addEventListener('click', (e) => {
  const panel = document.getElementById('notifPanel');
  const bell = e.target.closest('.notif-bell');
  if (panel && !bell && !panel.contains(e.target)) {
    panel.classList.remove('open');
  }
});
</script>