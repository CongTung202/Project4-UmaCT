<?php
require_once '../admin/includes/header.php';
require_once '../models/dashboard_model.php';

$stats = getDashboardStats();

if (!$stats) {
    die("<div style='margin:20px;'><h2 style='color:red;'>Lỗi kết nối đến máy chủ API! Vui lòng khởi động lại Python.</h2></div>");
}

$summary = $stats['summary'];
$chartData = $stats['chart'];
$recentOrders = $stats['recent_orders'];

// Xử lý dữ liệu mảng cho Javascript vẽ biểu đồ
$chartLabels = [];
$chartValues = [];
foreach ($chartData as $row) {
    $chartLabels[] = $row['month'];
    $chartValues[] = $row['revenue'];
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dashboard-container { padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .card-row { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
    .stat-card { flex: 1; min-width: 200px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid; display: flex; align-items: center; justify-content: space-between;}
    .card-blue { border-color: #007bff; }
    .card-green { border-color: #28a745; }
    .card-yellow { border-color: #ffc107; }
    .card-red { border-color: #dc3545; }
    .stat-title { font-size: 14px; color: #6c757d; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;}
    .stat-value { font-size: 24px; font-weight: bold; color: #343a40; margin: 0;}
    .stat-icon { font-size: 40px; opacity: 0.2; }
    
    .content-row { display: flex; gap: 20px; flex-wrap: wrap; }
    .chart-container { flex: 2; min-width: 400px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .table-container { flex: 1; min-width: 300px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: #fff;}
    .bg-success { background: #28a745; } .bg-warning { background: #ffc107; color: #000; }
</style>

<div class="dashboard-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Tổng quan Hệ thống</h2>
        <a href="export_csv.php" class="btn btn-add" style="background-color: #17a2b8; padding: 10px 20px;"><i class="fas fa-download"></i> Xuất báo cáo (CSV)</a>
    </div>

    <div class="card-row">
        <div class="stat-card card-blue">
            <div>
                <div class="stat-title">Tổng Doanh Thu</div>
                <div class="stat-value" style="color:#007bff;"><?= number_format($summary['revenue'], 0, ',', '.') ?> đ</div>
            </div>
            <div class="stat-icon">💰</div>
        </div>
        <div class="stat-card card-green">
            <div>
                <div class="stat-title">Số Đơn Hàng</div>
                <div class="stat-value"><?= number_format($summary['orders']) ?></div>
            </div>
            <div class="stat-icon">🛒</div>
        </div>
        <div class="stat-card card-yellow">
            <div>
                <div class="stat-title">Khách Hàng</div>
                <div class="stat-value"><?= number_format($summary['users']) ?></div>
            </div>
            <div class="stat-icon">👥</div>
        </div>
        <div class="stat-card card-red">
            <div>
                <div class="stat-title">Sản Phẩm</div>
                <div class="stat-value"><?= number_format($summary['products']) ?></div>
            </div>
            <div class="stat-icon">📦</div>
        </div>
    </div>

    <div class="content-row">
        <div class="chart-container">
            <h3 style="margin-top:0; color:#333;">Biểu đồ doanh thu 6 tháng gần nhất</h3>
            <canvas id="revenueChart" height="100"></canvas>
        </div>

        <div class="table-container">
            <h3 style="margin-top:0; color:#333;">Đơn hàng mới nhất</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <?php foreach($recentOrders as $order): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px 0;">
                        <strong>#<?= $order['id'] ?></strong> - <?= htmlspecialchars($order['full_name']) ?><br>
                        <span style="font-size: 12px; color: #888;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                    </td>
                    <td style="text-align: right; font-weight: bold; color: red;">
                        <?= number_format($order['total_price'], 0, ',', '.') ?>đ
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($recentOrders)): ?>
                    <tr><td>Chưa có đơn hàng nào.</td></tr>
                <?php endif; ?>
            </table>
            <div style="text-align: center; margin-top: 15px;">
                <a href="orders/index.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Xem tất cả đơn hàng &rarr;</a>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'bar', // Có thể đổi thành 'line' nếu bạn thích biểu đồ đường
        data: {
            // Nhúng mảng PHP vào biến Javascript bằng hàm json_encode
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: <?= json_encode($chartValues) ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php require_once '../admin/includes/footer.php'; ?>