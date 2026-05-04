<?php 
require_once 'includes/header.php'; 
require_once '../models/order_model.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$detail = getOrderDetail($id);

if (!$detail || $detail['order_info']['user_id'] != $_SESSION['user']['id']) {
    die("<div class='main-content'><h2>Đơn hàng không tồn tại!</h2></div>");
}

$info = $detail['order_info'];
$items = $detail['items'];
?>

<div style="width: 100%;">
    <a href="order_history.php" style="text-decoration: none; color: #888; font-size: 14px;">
        <i class="fas fa-arrow-left"></i> Quay lại lịch sử
    </a>
    <h2 style="margin: 20px 0;">Chi tiết đơn hàng #<?= $id ?></h2>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
        <div style="background: #fff; padding: 20px; border-radius: 8px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #eee; color: #666; font-size: 14px;">
                        <th style="padding: 10px; text-align: left;">Sản phẩm</th>
                        <th style="padding: 10px; text-align: center;">Đơn giá</th>
                        <th style="padding: 10px; text-align: center;">Số lượng</th>
                        <th style="padding: 10px; text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr style="border-bottom: 1px solid #f9f9f9;">
                        <td style="padding: 15px 10px; display: flex; align-items: center; gap: 15px;">
                            <img src="<?= $item['image_url'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <span style="font-size: 14px; font-weight: 600;"><?= $item['product_name'] ?></span>
                        </td>
                        <td style="text-align: center;"><?= number_format($item['price_at_purchase'], 0, ',', '.') ?>đ</td>
                        <td style="text-align: center;">x<?= $item['quantity'] ?></td>
                        <td style="text-align: right; font-weight: bold; color: #ff3333;">
                            <?= number_format($item['price_at_purchase'] * $item['quantity'], 0, ',', '.') ?>đ
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="background: #fff; padding: 25px; border-radius: 8px; height: fit-content;">
            <h4 style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Người nhận</h4>
            <p style="margin-bottom: 8px; font-weight: bold;"><?= $info['full_name'] ?></p>
            <p style="margin-bottom: 8px; font-size: 14px; color: #666;"><?= $info['phone'] ?></p>
            <p style="font-size: 13px; color: #888; line-height: 1.5;"><?= $info['shipping_address'] ?></p>

            <div style="margin-top: 25px; border-top: 2px dashed #eee; padding-top: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Tổng tiền sản phẩm:</span>
                    <span style="font-weight: bold;"><?= number_format($info['total_price'], 0, ',', '.') ?>đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 18px; margin-top: 15px;">
                    <span style="font-weight: 800;">TỔNG CỘNG:</span>
                    <span style="font-weight: 800; color: #ff3333;"><?= number_format($info['total_price'], 0, ',', '.') ?>đ</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>