<?php
// ===== الإضافة الأساسية: يجب بدء الجلسة في بداية الملف =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// =======================================================

require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');
// error_reporting(0); // It's better to log errors than to hide them completely.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $response = ['success' => true, 'data' => []];

    // Helper function to execute queries safely
    function fetchAll($conn, $sql, $params = [], $types = "") {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception("Prepare failed: " . $conn->error);
        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    // Helper function to execute a query and return a single row
    function fetchRow($conn, $sql, $params = [], $types = "") {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception("Prepare failed: " . $conn->error);
        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }
    
    // --- Time Periods ---
    $today_date = date('Y-m-d');
    $month_start = date('Y-m-01');
    $year_start = date('Y-01-01');

    // --- SUMMARY CALCULATIONS ---
    // 1. Today's Summary
    $today_sales_data = fetchRow($conn, "SELECT COALESCE(SUM(profit), 0) as profit FROM sales WHERE user_id = ? AND DATE(created_at) = ? AND is_deleted = 0", [$user_id, $today_date], "is");
    $today_expenses_data = fetchRow($conn, "SELECT COALESCE(SUM(amount), 0) as expenses FROM expenses WHERE user_id = ? AND DATE(created_at) = ? AND is_deleted = 0", [$user_id, $today_date], "is");
    $response['data']['today']['net_profit'] = (float)($today_sales_data['profit'] ?? 0) - (float)($today_expenses_data['expenses'] ?? 0);

    // 2. Month's Summary
    $month_sales_sql = "SELECT COALESCE(SUM(IF(final_price IS NOT NULL AND final_price >= 0, final_price, total_price)), 0) as gross, COALESCE(SUM(profit), 0) as profit FROM sales WHERE user_id = ? AND IF(sale_date IS NOT NULL AND sale_date != '0000-00-00', sale_date, created_at) >= ? AND is_deleted = 0";
    $month_sales_data = fetchRow($conn, $month_sales_sql, [$user_id, $month_start], "is");
    
    $month_expenses_sql = "SELECT COALESCE(SUM(amount), 0) as expenses FROM expenses WHERE user_id = ? AND IF(expense_date IS NOT NULL AND expense_date != '0000-00-00', expense_date, created_at) >= ? AND is_deleted = 0";
    $month_expenses_data = fetchRow($conn, $month_expenses_sql, [$user_id, $month_start], "is");

    $month_gross_revenue = (float)($month_sales_data['gross'] ?? 0);
    $month_profit = (float)($month_sales_data['profit'] ?? 0);
    $month_total_expenses = (float)($month_expenses_data['expenses'] ?? 0);
    $month_net_profit = $month_profit - $month_total_expenses;
    $response['data']['month'] = [
        "gross_revenue" => $month_gross_revenue,
        "expenses" => $month_total_expenses,
        "net_profit" => $month_net_profit,
        "profit_margin" => ($month_gross_revenue > 0) ? round(($month_net_profit / $month_gross_revenue) * 100, 2) : 0
    ];
    
    // 3. Year's Summary
    $year_sales_sql = "SELECT COALESCE(SUM(profit), 0) as profit FROM sales WHERE user_id = ? AND IF(sale_date IS NOT NULL AND sale_date != '0000-00-00', sale_date, created_at) >= ? AND is_deleted = 0";
    $year_sales_data = fetchRow($conn, $year_sales_sql, [$user_id, $year_start], "is");
    
    $year_expenses_sql = "SELECT COALESCE(SUM(amount), 0) as expenses FROM expenses WHERE user_id = ? AND IF(expense_date IS NOT NULL AND expense_date != '0000-00-00', expense_date, created_at) >= ? AND is_deleted = 0";
    $year_expenses_data = fetchRow($conn, $year_expenses_sql, [$user_id, $year_start], "is");
    
    $response['data']['year']['net_profit'] = (float)($year_sales_data['profit'] ?? 0) - (float)($year_expenses_data['expenses'] ?? 0);

    // --- CATEGORY ANALYSIS (This Month) ---
    // 🔧 FIX: This is the final, robust query. It joins with inventory for products and unions with services.
    $category_sql = "
        (SELECT 
            i.type as category, 
            SUM(IF(s.final_price IS NOT NULL AND s.final_price >= 0, s.final_price, s.total_price)) as category_revenue 
         FROM sales s
         JOIN inventory i ON s.inventory_id = i.id
         WHERE 
            s.user_id = ? AND IF(s.sale_date IS NOT NULL AND s.sale_date != '0000-00-00', s.sale_date, s.created_at) >= ? 
            AND s.is_deleted = 0 AND s.inventory_id IS NOT NULL
         GROUP BY i.type)
        UNION ALL
        (SELECT 
            'service' as category, 
            SUM(IF(final_price IS NOT NULL AND final_price >= 0, final_price, total_price)) as category_revenue 
         FROM sales
         WHERE 
            user_id = ? AND IF(sale_date IS NOT NULL AND sale_date != '0000-00-00', sale_date, created_at) >= ? 
            AND is_deleted = 0 AND sale_type = 'service'
        )
    ";
    $response['data']['category_breakdown'] = fetchAll($conn, $category_sql, [$user_id, $month_start, $user_id, $month_start], "isis");


    // --- TOP PRODUCTS (This Month) ---
    $top_products_sql = "SELECT item_name, SUM(quantity) as total_qty, SUM(IF(final_price IS NOT NULL AND final_price >= 0, final_price, total_price)) as revenue FROM sales WHERE user_id = ? AND IF(sale_date IS NOT NULL AND sale_date != '0000-00-00', sale_date, created_at) >= ? AND is_deleted = 0 AND sale_type != 'service' GROUP BY item_name ORDER BY revenue DESC LIMIT 5";
    $response['data']['top_products'] = fetchAll($conn, $top_products_sql, [$user_id, $month_start], "is");
    
    // --- TIME SERIES DATA ---
    $interval_days = isset($_GET['interval']) && in_array($_GET['interval'], [30, 90]) ? (int)$_GET['interval'] : 30;
    $start_date_interval = date('Y-m-d', strtotime("-{$interval_days} days"));

    $daily_sales_sql = "SELECT DATE(IF(sale_date IS NOT NULL AND sale_date != '0000-00-00', sale_date, created_at)) as date, SUM(IF(final_price IS NOT NULL AND final_price >= 0, final_price, total_price)) as gross_revenue, SUM(profit) as profit FROM sales WHERE user_id = ? AND IF(sale_date IS NOT NULL AND sale_date != '0000-00-00', sale_date, created_at) >= ? AND is_deleted = 0 GROUP BY date";
    $daily_sales_data = fetchAll($conn, $daily_sales_sql, [$user_id, $start_date_interval], "is");
    
    $daily_expenses_sql = "SELECT DATE(IF(expense_date IS NOT NULL AND expense_date != '0000-00-00', expense_date, created_at)) as date, SUM(amount) as expenses FROM expenses WHERE user_id = ? AND IF(expense_date IS NOT NULL AND expense_date != '0000-00-00', expense_date, created_at) >= ? AND is_deleted = 0 GROUP BY date";
    $daily_expenses_data = fetchAll($conn, $daily_expenses_sql, [$user_id, $start_date_interval], "is");
    
    $merged_daily = [];
    $all_dates = [];

    foreach ($daily_sales_data as $row) { if($row['date']) $all_dates[$row['date']] = true; }
    foreach ($daily_expenses_data as $row) { if($row['date']) $all_dates[$row['date']] = true; }
    ksort($all_dates);

    foreach (array_keys($all_dates) as $date) {
        $merged_daily[$date] = ['gross_revenue' => 0, 'net_profit' => 0, 'total_expenses' => 0];
    }
    
    foreach ($daily_sales_data as $row) {
        if($row['date']) {
            $merged_daily[$row['date']]['gross_revenue'] = (float)$row['gross_revenue'];
            $merged_daily[$row['date']]['net_profit'] = (float)$row['profit'];
        }
    }
    
    foreach ($daily_expenses_data as $row) {
        if($row['date']) {
            $merged_daily[$row['date']]['total_expenses'] = (float)$row['expenses'];
            $merged_daily[$row['date']]['net_profit'] -= (float)$row['expenses'];
        }
    }
    
    $response['data']['timeseries']['daily'] = [];
    foreach ($merged_daily as $date => $values) {
        $response['data']['timeseries']['daily'][] = array_merge(['date' => $date], $values);
    }
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
