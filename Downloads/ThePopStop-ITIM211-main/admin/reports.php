<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

$report_type = isset($_GET['type']) ? sanitize($_GET['type']) : 'weekly_sales';

$today = date('Y-m-d');

if (in_array($report_type, ['monthly_sales', 'profit_expense'])) {
    $default_start = date('Y-m-01');
    $default_end = date('Y-m-t');
} else {
    $default_start = date('Y-m-d', strtotime('-7 days'));
    $default_end = $today;
}

$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : $default_start;
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : $default_end;

$report_data = [];
$report_title = '';
$expense_breakdown = [];

switch ($report_type) {
    case 'weekly_sales':
        $report_title = 'Weekly Sales Report';
        
        $report_data = $conn->query("\n            SELECT t.order_date AS date,\n                   COUNT(*) AS order_count,\n                   SUM(t.order_total) AS total_sales\n            FROM (\n                SELECT o.order_id,\n                       DATE(o.order_date) AS order_date,\n                       COALESCE(SUM(oi.quantity * oi.unit_price), 0) - COALESCE(o.discount_amount, 0) AS order_total\n                FROM orders o\n                LEFT JOIN order_items oi ON o.order_id = oi.order_id\n                WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'\n                  AND o.status != 'Cancelled'\n                GROUP BY o.order_id, DATE(o.order_date), o.discount_amount\n            ) t\n            GROUP BY t.order_date\n            ORDER BY t.order_date\n        ")->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'monthly_sales':
        $report_title = 'Monthly Sales Report';
        
        $report_data = $conn->query("\n            SELECT t.order_date AS date,\n                   COUNT(*) AS order_count,\n                   SUM(t.order_total) AS total_sales\n            FROM (\n                SELECT o.order_id,\n                       DATE(o.order_date) AS order_date,\n                       COALESCE(SUM(oi.quantity * oi.unit_price), 0) - COALESCE(o.discount_amount, 0) AS order_total\n                FROM orders o\n                LEFT JOIN order_items oi ON o.order_id = oi.order_id\n                WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'\n                  AND o.status != 'Cancelled'\n                GROUP BY o.order_id, DATE(o.order_date), o.discount_amount\n            ) t\n            GROUP BY t.order_date\n            ORDER BY t.order_date\n        ")->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'profit_expense':
        $report_title = 'Monthly Profit & Expense Report';
        
        $revenue_row = $conn->query("\n            SELECT COALESCE(SUM(order_total), 0) AS total\n            FROM (\n                SELECT o.order_id,\n                       COALESCE(SUM(oi.quantity * oi.unit_price), 0) - COALESCE(o.discount_amount, 0) AS order_total\n                FROM orders o\n                LEFT JOIN order_items oi ON o.order_id = oi.order_id\n                WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'\n                  AND o.status != 'Cancelled'\n                GROUP BY o.order_id, o.discount_amount\n            ) t\n        ")->fetch_assoc();
        $revenue = $revenue_row['total'] ?? 0;
        
        $po_expenses_row = $conn->query("\n            SELECT COALESCE(SUM(oi.quantity * oi.unit_cost), 0) AS total\n            FROM purchase_orders po\n            JOIN purchase_order_items oi ON po.po_id = oi.po_id\n            WHERE po.order_date BETWEEN '$start_date' AND '$end_date'\n              AND po.status = 'Received'\n        ")->fetch_assoc();
        $po_expenses = $po_expenses_row['total'] ?? 0;
        
        $base_operating = $conn->query("
            SELECT SUM(amount) as total
            FROM expenses
        ")->fetch_assoc()['total'] ?? 0;

        $start_year = (int)substr($start_date, 0, 4);
        $start_month = (int)substr($start_date, 5, 2);
        $end_year = (int)substr($end_date, 0, 4);
        $end_month = (int)substr($end_date, 5, 2);
        $months_count = (($end_year - $start_year) * 12) + ($end_month - $start_month) + 1;
        if ($months_count < 1) {
            $months_count = 1;
        }

        $operating_expenses = $base_operating * $months_count;

        $expense_breakdown = $conn->query("
            SELECT ec.name, SUM(e.amount) as total
            FROM expenses e
            JOIN expense_categories ec ON e.category_id = ec.category_id
            GROUP BY e.category_id, ec.name
            ORDER BY total DESC
        ")->fetch_all(MYSQLI_ASSOC);

        foreach ($expense_breakdown as &$expense_row) {
            $expense_row['total'] = $expense_row['total'] * $months_count;
        }
        unset($expense_row);
        
        $total_expenses = $po_expenses + $operating_expenses;
        $profit = $revenue - $total_expenses;
        
        $report_data = [
            ['label' => 'Total Revenue (Selected Period)', 'amount' => $revenue, 'type' => 'summary'],
            ['label' => 'Purchase Orders (Cost of Goods)', 'amount' => $po_expenses, 'type' => 'expense'],
            ['label' => 'Monthly Operating Expenses', 'amount' => $operating_expenses, 'type' => 'expense'],
            ['label' => 'Total Expenses (Monthly + COGS)', 'amount' => $total_expenses, 'type' => 'summary'],
            ['label' => 'Net Profit for Period', 'amount' => $profit, 'type' => 'summary']
        ];
        break;
        
    case 'top_products':
        $report_title = 'Top Selling Products';
        
        $report_data = $conn->query("\n            SELECT p.name, p.brand, p.series,\n                   SUM(oi.quantity) AS total_sold,\n                   SUM(oi.quantity * oi.unit_price) AS total_revenue\n            FROM order_items oi\n            JOIN products p ON oi.product_id = p.product_id\n            JOIN orders o ON oi.order_id = o.order_id\n            WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'\n              AND o.status != 'Cancelled'\n            GROUP BY oi.product_id\n            ORDER BY total_sold DESC\n            LIMIT 10\n        ")->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'customer_orders':
        $report_title = 'Customer Orders Report';
        
        $report_data = $conn->query("\n            SELECT u.full_name, u.email,\n                   COUNT(o.order_id) AS order_count,\n                   COALESCE(SUM(ot.order_total), 0) AS total_spent\n            FROM users u\n            LEFT JOIN orders o ON u.user_id = o.user_id\n            LEFT JOIN (\n                SELECT o2.order_id,\n                       COALESCE(SUM(oi.quantity * oi.unit_price), 0) - COALESCE(o2.discount_amount, 0) AS order_total\n                FROM orders o2\n                LEFT JOIN order_items oi ON o2.order_id = oi.order_id\n                GROUP BY o2.order_id, o2.discount_amount\n            ) ot ON ot.order_id = o.order_id\n            WHERE u.role = 'customer'\n              AND (o.order_date BETWEEN '$start_date' AND '$end_date' OR o.order_id IS NULL)\n            GROUP BY u.user_id\n            ORDER BY total_spent DESC\n        ")->fetch_all(MYSQLI_ASSOC);
        break;
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../index.php" class="logo">The Pop Stop</a>
            <nav>
                <ul>
                    <li><a href="../index.php">View Site</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-layout">
        <div class="admin-sidebar">
            <h3>Admin Menu</h3>
            <ul>
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php">📦 Products</a></li>
                <li><a href="orders.php">🛍️ Orders</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="suppliers.php">🏭 Suppliers</a></li>
                <li><a href="purchase-orders.php">📋 Purchase Orders</a></li>
                <li><a href="discounts.php">🎟️ Discounts</a></li>
                <li><a href="reports.php" class="active">📈 Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <h1 style="margin-bottom: 2rem; color: var(--dark-brown);">Reports</h1>
            
            <!-- Report Selection -->
            <div class="card" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">Select Report Type</h3>
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Report Type</label>
                        <select name="type" onchange="this.form.submit()">
                            <option value="weekly_sales" <?php echo $report_type === 'weekly_sales' ? 'selected' : ''; ?>>Weekly Sales</option>
                            <option value="monthly_sales" <?php echo $report_type === 'monthly_sales' ? 'selected' : ''; ?>>Monthly Sales</option>
                            <option value="profit_expense" <?php echo $report_type === 'profit_expense' ? 'selected' : ''; ?>>Profit & Expense</option>
                            <option value="top_products" <?php echo $report_type === 'top_products' ? 'selected' : ''; ?>>Top Products</option>
                            <option value="customer_orders" <?php echo $report_type === 'customer_orders' ? 'selected' : ''; ?>>Customer Orders</option>
                        </select>
                    </div>
                    
                    <?php if (in_array($report_type, ['weekly_sales', 'monthly_sales', 'profit_expense', 'top_products', 'customer_orders'])): ?>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Start Date</label>
                            <input type="date" name="start_date" value="<?php echo $start_date; ?>" onchange="this.form.submit()">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>End Date</label>
                            <input type="date" name="end_date" value="<?php echo $end_date; ?>" onchange="this.form.submit()">
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Report Display -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="color: var(--dark-brown);"><?php echo $report_title; ?></h2>
                    <button onclick="window.print()" class="btn btn-secondary btn-small">Print Report</button>
                </div>
                
                <p style="margin-bottom: 1.5rem; color: var(--secondary);">
                    Period: <?php echo formatDate($start_date) . ' to ' . formatDate($end_date); ?>
                </p>
                
                <?php if ($report_type === 'weekly_sales' || $report_type === 'monthly_sales'): ?>
                    <?php if (count($report_data) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Orders</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_orders = 0;
                                $total_sales = 0;
                                foreach ($report_data as $row): 
                                    $total_orders += $row['order_count'];
                                    $total_sales += $row['total_sales'];
                                ?>
                                    <tr>
                                        <td><?php echo formatDate($row['date']); ?></td>
                                        <td><?php echo $row['order_count']; ?></td>
                                        <td><?php echo formatCurrency($row['total_sales']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background: var(--light-beige); font-weight: bold;">
                                    <td>TOTAL</td>
                                    <td><?php echo $total_orders; ?></td>
                                    <td><?php echo formatCurrency($total_sales); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No sales data for this period.</div>
                    <?php endif; ?>
                    
                <?php elseif ($report_type === 'profit_expense'): ?>
                    <div style="max-width: 600px;">
                        <?php foreach ($report_data as $item): ?>
                            <div style="display: flex; justify-content: space-between; padding: 1rem; border-bottom: 2px solid var(--border); font-size: 1.1rem; <?php echo $item['type'] === 'summary' ? 'background: var(--light-beige);' : ''; ?>">
                                <strong><?php echo $item['label']; ?>:</strong>
                                <span style="color: <?php echo $item['label'] === 'Net Profit' ? ($item['amount'] >= 0 ? '#27AE60' : '#E74C3C') : 'var(--text-dark)'; ?>; font-weight: bold;">
                                    <?php echo formatCurrency($item['amount']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($expense_breakdown) > 0): ?>
                        <div style="margin-top: 2rem;">
                            <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Operating Expenses Breakdown</h3>
                            <table style="max-width: 600px;">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th style="text-align: right;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expense_breakdown as $expense): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($expense['name']); ?></td>
                                            <td style="text-align: right;"><?php echo formatCurrency($expense['total']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($report_type === 'top_products'): ?>
                    <?php if (count($report_data) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Product</th>
                                    <th>Brand/Series</th>
                                    <th>Units Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($report_data as $row): 
                                ?>
                                    <tr>
                                        <td><?php echo $rank++; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['brand']) . ' - ' . htmlspecialchars($row['series']); ?></td>
                                        <td><?php echo $row['total_sold']; ?></td>
                                        <td><?php echo formatCurrency($row['total_revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No product sales data for this period.</div>
                    <?php endif; ?>
                    
                <?php elseif ($report_type === 'customer_orders'): ?>
                    <?php if (count($report_data) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo $row['order_count']; ?></td>
                                        <td><?php echo formatCurrency($row['total_spent'] ?? 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No customer data available.</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
