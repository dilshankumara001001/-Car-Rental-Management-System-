<?php
/**
 * 🚗 Car Rental — Shared Helper Functions
 * Total: 20 functions (dupe-safe wrapper)
 */

/* ─────────── 1. Escape Output (XSS protect) ─────────── */
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/* ─────────── 2. Redirect with BASE_URL ─────────── */
if (!function_exists('redirect')) {
    function redirect($path) {
        header("Location: " . BASE_URL . ltrim($path, '/'));
        exit();
    }
}

/* ─────────── 3. Flash Message (Session) ─────────── */
if (!function_exists('set_flash')) {
    function set_flash($type, $msg) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }
}
if (!function_exists('get_flash')) {
    function get_flash() {
        if (!isset($_SESSION['flash'])) return null;
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
}
if (!function_exists('show_flash')) {
    function show_flash() {
        $f = get_flash();
        if (!$f) return;
        $colors = [
            'success'=>'#dcfce7;color:#166534',
            'error'  =>'#fee2e2;color:#991b1b',
            'warning'=>'#fef3c7;color:#92400e',
            'info'   =>'#dbeafe;color:#1e40af'
        ];
        $c = $colors[$f['type']] ?? $colors['info'];
        echo "<div style='background:{$c};padding:10px;border-radius:6px;margin-bottom:15px;font-weight:bold'>"
           . e($f['msg']) . "</div>";
    }
}

/* ─────────── 4. Format Money ─────────── */
if (!function_exists('money')) {
    function money($amount) {
        return 'Rs. ' . number_format((float)$amount, 2);
    }
}

/* ─────────── 5. Format Date (Sri Lankan) ─────────── */
if (!function_exists('fdate')) {
    function fdate($date, $format = 'd M Y') {
        return $date ? date($format, strtotime($date)) : '—';
    }
}

/* ─────────── 6. Days Between Two Dates ─────────── */
if (!function_exists('days_between')) {
    function days_between($from, $to = null) {
        $d1 = new DateTime($from);
        $d2 = new DateTime($to ?: date('Y-m-d'));
        return max(1, (int)$d1->diff($d2)->days);
    }
}

/* ─────────── 7. Status Badge HTML ─────────── */
if (!function_exists('badge')) {
    function badge($status) {
        $s = strtolower(str_replace(' ', '', $status));
        return "<span class='badge {$s}'>" . e($status) . "</span>";
    }
}

/* ─────────── 8. Check Overdue Rental ─────────── */
if (!function_exists('is_overdue')) {
    function is_overdue($rental_date, $days_allowed = 7) {
        return days_between($rental_date) > $days_allowed;
    }
}

/* ─────────── 9. Generate Rental Invoice No ─────────── */
if (!function_exists('invoice_no')) {
    function invoice_no($rental_id) {
        return 'INV-' . date('Y') . '-' . str_pad($rental_id, 5, '0', STR_PAD_LEFT);
    }
}

/* ─────────── 10. Get Single Row ─────────── */
if (!function_exists('db_row')) {
    function db_row($conn, $sql, $params = [], $types = '') {
        $stmt = mysqli_prepare($conn, $sql);
        if ($params) mysqli_stmt_bind_param($stmt, $types ?: str_repeat('s', count($params)), ...$params);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }
}

/* ─────────── 11. Get All Rows ─────────── */
if (!function_exists('db_all')) {
    function db_all($conn, $sql, $params = [], $types = '') {
        $stmt = mysqli_prepare($conn, $sql);
        if ($params) mysqli_stmt_bind_param($stmt, $types ?: str_repeat('s', count($params)), ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
        return $rows;
    }
}

/* ─────────── 12. Get Single Value ─────────── */
if (!function_exists('db_value')) {
    function db_value($conn, $sql, $params = [], $types = '') {
        $row = db_row($conn, $sql, $params, $types);
        return $row ? array_values($row)[0] : null;
    }
}

/* ─────────── 13. Pagination Data ─────────── */
if (!function_exists('paginate')) {
    function paginate($conn, $baseSql, $countSql, $params, $types, $per_page = 10) {
        $page  = max(1, (int)($_GET['p'] ?? 1));
        $total = (int)db_value($conn, $countSql, $params, $types);
        $pages = max(1, ceil($total / $per_page));
        $page  = min($page, $pages);
        $offset = ($page - 1) * $per_page;
        return [
            'rows'  => db_all($conn, $baseSql . " LIMIT $per_page OFFSET $offset", $params, $types),
            'page'  => $page,
            'pages' => $pages,
            'total' => $total,
        ];
    }
}

/* ─────────── 14. Pagination Links HTML ─────────── */
if (!function_exists('pagination_links')) {
    function pagination_links($data, $extraQuery = '') {
        if ($data['pages'] <= 1) return;
        echo "<div style='margin-top:15px;text-align:center'>";
        for ($i = 1; $i <= $data['pages']; $i++) {
            $active = $i == $data['page'] ? 'background:#1f2937' : '';
            $sep = $extraQuery ? '&' : '?';
            echo "<a class='btn' style='margin:2px;{$active}' href='?p={$i}{$sep}{$extraQuery}'>{$i}</a>";
        }
        echo "</div>";
    }
}

/* ─────────── 15. Build Query String ─────────── */
if (!function_exists('build_qs')) {
    function build_qs($overrides = []) {
        $qs = array_merge($_GET, $overrides);
        return http_build_query($qs);
    }
}

/* ─────────── 16. Sort Link ─────────── */
if (!function_exists('sort_link')) {
    function sort_link($col, $label, $current_col, $current_dir) {
        $dir   = ($current_col === $col && $current_dir === 'asc') ? 'desc' : 'asc';
        $arrow = $current_col === $col ? ($current_dir === 'asc' ? ' ▲' : ' ▼') : '';
        $qs    = build_qs(['sort' => $col, 'dir' => $dir, 'p' => 1]);
        return "<a href='?{$qs}' style='color:inherit;text-decoration:none'>{$label}{$arrow}</a>";
    }
}

/* ─────────── 17. CSV Export ─────────── */
if (!function_exists('export_csv')) {
    function export_csv($filename, $headers, $rows) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) fputcsv($out, $row);
        fclose($out);
        exit();
    }
}

/* ─────────── 18. Search Filter SQL Helper ─────────── */
if (!function_exists('search_where')) {
    function search_where($columns, $keyword) {
        if (empty($keyword)) return ['', []];
        $parts = [];
        foreach ($columns as $c) $parts[] = "$c LIKE ?";
        return ['(' . implode(' OR ', $parts) . ')', array_fill(0, count($columns), "%$keyword%")];
    }
}

/* ─────────── 19. Check User Permission ─────────── */
if (!function_exists('can')) {
    function can($action) {
        $role  = $_SESSION['role'] ?? 'guest';
        $perms = [
            'admin' => ['*'],
            'staff' => ['view', 'rent', 'return', 'add_customer', 'add_rental'],
        ];
        if (!isset($perms[$role])) return false;
        return in_array('*', $perms[$role]) || in_array($action, $perms[$role]);
    }
}

/* ─────────── 20. Activity Log ─────────── */
if (!function_exists('log_activity')) {
    function log_activity($conn, $action, $details = '') {
        $user = $_SESSION['username'] ?? 'system';
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS activity_log (
            log_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50),
            action VARCHAR(100),
            details TEXT,
            ip VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = mysqli_prepare($conn,
            "INSERT INTO activity_log (username, action, details, ip) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssss", $user, $action, $details, $ip);
        @mysqli_stmt_execute($stmt);
    }
}


/* ═══════════════════════════════════════════════════
 *  💳 PAYMENT FUNCTIONS
 * ═══════════════════════════════════════════════════ */

if (!function_exists('payment_badge')) {
    function payment_badge($status) {
        $s = strtolower($status);
        return "<span class='badge {$s}'>" . e($status) . "</span>";
    }
}

if (!function_exists('mark_payment')) {
    function mark_payment($conn, $rental_id, $status, $method = 'Cash') {
        $stmt = mysqli_prepare($conn,
          "UPDATE rentals SET payment_status=?, payment_date=CURDATE(),
                              payment_method=?, paid_amount=total_amount
           WHERE rental_id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $status, $method, $rental_id);
        return mysqli_stmt_execute($stmt);
    }
}

/* ═══════════════════════════════════════════════════
 *  🔔 NOTIFICATION FUNCTIONS
 * ═══════════════════════════════════════════════════ */

if (!function_exists('add_notification')) {
    function add_notification($conn, $type, $title, $message, $user_id = null) {
        $stmt = mysqli_prepare($conn,
          "INSERT INTO notifications (user_id, type, title, message) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $type, $title, $message);
        return mysqli_stmt_execute($stmt);
    }
}

if (!function_exists('get_unread_count')) {
    function get_unread_count($conn, $user_id = null) {
        if ($user_id) {
            $row = db_row($conn,
              "SELECT COUNT(*) c FROM notifications WHERE is_read=0
               AND (user_id=? OR user_id IS NULL)", [$user_id], 'i');
        } else {
            $row = db_row($conn,
              "SELECT COUNT(*) c FROM notifications WHERE is_read=0
               AND user_id IS NULL");
        }
        return (int)($row['c'] ?? 0);
    }
}

if (!function_exists('check_overdue_rentals')) {
    function check_overdue_rentals($conn, $days_limit = 7) {
        $overdue = db_all($conn,
          "SELECT r.rental_id, c.car_name, cu.name AS cust_name,
                  DATEDIFF(CURDATE(), r.rental_date) AS days_out
           FROM rentals r
           JOIN cars c ON c.car_id=r.car_id
           JOIN customers cu ON cu.customer_id=r.customer_id
           WHERE r.rental_status='Rented'
             AND DATEDIFF(CURDATE(), r.rental_date) > ?", [$days_limit], 'i');

        foreach ($overdue as $o) {
            $exists = db_row($conn,
              "SELECT notif_id FROM notifications
               WHERE type='overdue' AND title LIKE ?
               AND DATE(created_at)=CURDATE()",
              ["%#{$o['rental_id']}%"], 's');

            if (!$exists) {
                add_notification($conn, 'overdue',
                  "⚠️ Rental #{$o['rental_id']} Overdue",
                  "{$o['car_name']} rented to {$o['cust_name']} is {$o['days_out']} days overdue."
                );
            }
        }
        return count($overdue);
    }
}

/* ═══════════════════════════════════════════════════
 *  🌙 THEME
 * ═══════════════════════════════════════════════════ */

if (!function_exists('user_theme')) {
    function user_theme($conn) {
        $uid = $_SESSION['user_id'] ?? 0;
        if (!$uid) return 'light';
        $row = db_row($conn, "SELECT theme FROM users WHERE user_id=?", [$uid], 'i');
        return $row['theme'] ?? 'light';
    }
}

/* ═══════════════════════════════════════════════════
 *  🔍 REPORT FUNCTIONS
 * ═══════════════════════════════════════════════════ */

if (!function_exists('report_range')) {
    function report_range($conn, $from, $to) {
        return db_row($conn,
          "SELECT COUNT(*) AS total_rentals,
                  COALESCE(SUM(total_amount),0) AS revenue,
                  COALESCE(AVG(total_amount),0) AS avg_amount,
                  SUM(CASE WHEN payment_status='Paid' THEN 1 ELSE 0 END) AS paid_count,
                  SUM(CASE WHEN payment_status='Unpaid' THEN 1 ELSE 0 END) AS unpaid_count
           FROM rentals
           WHERE rental_status='Returned'
             AND return_date BETWEEN ? AND ?", [$from, $to], 'ss');
    }
}

if (!function_exists('monthly_data')) {
    function monthly_data($conn, $year) {
        return db_all($conn,
          "SELECT MONTH(return_date) AS m,
                  COUNT(*) AS rentals,
                  COALESCE(SUM(total_amount),0) AS revenue
           FROM rentals
           WHERE YEAR(return_date)=? AND rental_status='Returned'
           GROUP BY MONTH(return_date) ORDER BY m", [$year], 'i');
    }
}

if (!function_exists('yearly_data')) {
    function yearly_data($conn) {
        return db_all($conn,
          "SELECT YEAR(return_date) AS y,
                  COUNT(*) AS rentals,
                  COALESCE(SUM(total_amount),0) AS revenue
           FROM rentals
           WHERE rental_status='Returned'
           GROUP BY YEAR(return_date) ORDER BY y DESC");
    }
}

if (!function_exists('quarterly_data')) {
    function quarterly_data($conn, $year) {
        return db_all($conn,
          "SELECT QUARTER(return_date) AS q,
                  COUNT(*) AS rentals,
                  COALESCE(SUM(total_amount),0) AS revenue
           FROM rentals
           WHERE YEAR(return_date)=? AND rental_status='Returned'
           GROUP BY QUARTER(return_date) ORDER BY q", [$year], 'i');
    }
}