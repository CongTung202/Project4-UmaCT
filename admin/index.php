<?php
require_once '../admin/includes/header.php';
require_once '../models/dashboard_model.php';

$stats = getDashboardStats();

if (!$stats) {
    die("<div class='main-content'><h2 style='color:red;'>Lỗi kết nối đến máy chủ API! Vui lòng khởi động lại Python.</h2></div>");
}

$summary = $stats['summary'];
$recentOrders = $stats['recent_orders'];
?>

<style>
    /* Lưới các thẻ thống kê */
    .card-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: #fff; padding: 25px 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid; display: flex; align-items: center; justify-content: space-between; transition: transform 0.3s;}
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .card-blue { border-color: #007bff; }
    .card-green { border-color: #28a745; }
    .card-yellow { border-color: #ffc107; }
    .card-red { border-color: #dc3545; }
    
    .stat-title { font-size: 13px; color: #888; font-weight: bold; text-transform: uppercase; margin-bottom: 8px;}
    .stat-value { font-size: 26px; font-weight: 900; color: #333; margin: 0;}
    .stat-icon { font-size: 45px; opacity: 0.15; }
    
    /* Bố cục 2 cột bên dưới */
    .content-row { display: flex; gap: 20px; flex-wrap: wrap; }
    .table-container { flex: 2; min-width: 500px; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .action-container { flex: 1; min-width: 300px; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    
    /* Nút thao tác nhanh */
    .quick-action-btn {
        display: block; width: 100%; padding: 15px; margin-bottom: 12px; 
        background: #f8f9fa; border: 1px solid #ddd; border-radius: 6px; 
        color: #333; text-decoration: none; font-weight: bold; transition: 0.3s;
        text-align: left; box-sizing: border-box;
    }
    .quick-action-btn i { width: 30px; font-size: 18px; color: #ff3333; }
    .quick-action-btn:hover { background: #ff3333; color: #fff; border-color: #ff3333; }
    .quick-action-btn:hover i { color: #fff; }
</style>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2><i class="fas fa-chart-pie" style="color: #ff3333;"></i> Tổng quan Hệ thống</h2>
        <a href="export_csv.php" class="btn btn-add" style="margin: 0; background-color: #17a2b8; box-shadow: none;"><i class="fas fa-download"></i> Xuất báo cáo (CSV)</a>
    </div>

    <div class="card-row">
        <div class="stat-card card-blue">
            <div>
                <div class="stat-title">Tổng Doanh Thu</div>
                <div class="stat-value" style="color:#007bff;"><?= number_format($summary['revenue'], 0, ',', '.') ?>đ</div>
            </div>
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
        <div class="stat-card card-green">
            <div>
                <div class="stat-title">Đơn Hàng</div>
                <div class="stat-value"><?= number_format($summary['orders']) ?></div>
            </div>
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
        </div>
        <div class="stat-card card-yellow">
            <div>
                <div class="stat-title">Khách Hàng</div>
                <div class="stat-value"><?= number_format($summary['users']) ?></div>
            </div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="stat-card card-red">
            <div>
                <div class="stat-title">Sản Phẩm</div>
                <div class="stat-value"><?= number_format($summary['products']) ?></div>
            </div>
            <div class="stat-icon"><i class="fas fa-box-open"></i></div>
        </div>
    </div>

    <div class="content-row">
        
        <div class="table-container">
            <h3 style="margin-top:0; color:#333; border-bottom: 2px solid #ff3333; padding-bottom: 10px; margin-bottom: 20px;"><i class="fas fa-clock"></i> Đơn hàng mới nhất</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px; border-bottom: 2px solid #eee; text-align: left; color: #666; background: none;">Mã ĐH</th>
                        <th style="padding: 12px; border-bottom: 2px solid #eee; text-align: left; color: #666; background: none;">Khách hàng</th>
                        <th style="padding: 12px; border-bottom: 2px solid #eee; text-align: right; color: #666; background: none;">Tổng tiền</th>
                        <th style="padding: 12px; border-bottom: 2px solid #eee; text-align: center; color: #666; background: none;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentOrders as $order): ?>
                    <tr style="border-bottom: 1px solid #eee; transition: 0.3s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 15px 12px; font-weight: bold; color: #ff3333;">#<?= $order['id'] ?></td>
                        <td style="padding: 15px 12px;">
                            <strong><?= htmlspecialchars($order['full_name']) ?></strong><br>
                            <span style="font-size: 12px; color: #888;"><i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                        </td>
                        <td style="padding: 15px 12px; text-align: right; font-weight: bold; font-size: 15px;">
                            <?= number_format($order['total_price'], 0, ',', '.') ?>đ
                        </td>
                        <td style="padding: 15px 12px; text-align: center;">
                            <?php if($order['status'] == 'PENDING'): ?>
                                <span style="background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">CHỜ XỬ LÝ</span>
                            <?php elseif($order['status'] == 'PAID'): ?>
                                <span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">ĐÃ THANH TOÁN</span>
                            <?php elseif($order['status'] == 'CANCELLED'): ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">ĐÃ HỦY</span>
                            <?php else: ?>
                                <span style="background: #e2e3e5; color: #383d41; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;"><?= $order['status'] ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($recentOrders)): ?>
                        <tr><td colspan="4" style="padding: 30px; text-align: center; color: #888;">Chưa có đơn hàng nào phát sinh.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="text-align: center; margin-top: 20px;">
                <a href="orders/index.php" style="color: #ff3333; text-decoration: none; font-weight: bold; padding: 8px 15px; border: 1px solid #ff3333; border-radius: 4px; display: inline-block; transition: 0.3s;" onmouseover="this.style.background='#ff3333'; this.style.color='#fff';" onmouseout="this.style.background='transparent'; this.style.color='#ff3333';">Xem toàn bộ đơn hàng &rarr;</a>
            </div>
        </div>

        <div class="action-container">
            <h3 style="margin-top:0; color:#333; border-bottom: 2px solid #ff3333; padding-bottom: 10px; margin-bottom: 20px;"><i class="fas fa-bolt"></i> Thao tác nhanh</h3>
            
            <a href="products/create.php" class="quick-action-btn"><i class="fas fa-plus-circle"></i> Thêm Sản phẩm mới</a>
            <a href="orders/index.php?status_filter=PENDING" class="quick-action-btn"><i class="fas fa-box"></i> Xử lý Đơn hàng chờ</a>
            <a href="vouchers/create.php" class="quick-action-btn"><i class="fas fa-ticket-alt"></i> Tạo Mã giảm giá</a>
            <a href="users/index.php" class="quick-action-btn"><i class="fas fa-users-cog"></i> Quản lý Người dùng</a>
            <a href="articles/create.php" class="quick-action-btn"><i class="fas fa-edit"></i> Đăng Tin tức mới</a>
        </div>

    </div>
</div>

<?php require_once '../admin/includes/footer.php'; ?>