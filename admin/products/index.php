<?php
require_once '../../admin/includes/header.php';
require_once '../../models/product_model.php';
require_once '../../models/supplier_model.php';
require_once '../../models/category_model.php'; // Bổ sung model này để lấy danh sách danh mục cho ô Select

// 1. LẤY DỮ LIỆU BAN ĐẦU
$all_products = getAllProducts();
$categories = getAllCategories();

// 2. NHẬN THAM SỐ TÌM KIẾM TỪ URL (GET)
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$cat_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 5; // Số sản phẩm hiển thị trên 1 trang (bác có thể đổi thành 10)

// 3. XỬ LÝ LỌC SẢN PHẨM (FILTER)
$filtered_products = [];
foreach ($all_products as $p) {
    // Kiểm tra tên sản phẩm có chứa từ khóa không (Không phân biệt hoa thường)
    $match_keyword = empty($keyword) || mb_stripos($p['name'], $keyword, 0, 'UTF-8') !== false;
    
    // Kiểm tra danh mục
    $match_cat = empty($cat_id) || $p['category_id'] == $cat_id;
    
    if ($match_keyword && $match_cat) {
        $filtered_products[] = $p;
    }
}

// 4. XỬ LÝ PHÂN TRANG (PAGINATION)
$total_items = count($filtered_products);
$total_pages = ceil($total_items / $limit);
$offset = ($page - 1) * $limit;

// Cắt mảng để lấy đúng số sản phẩm của trang hiện tại
$products = array_slice($filtered_products, $offset, $limit);
?>

<style>
    /* CSS Phân trang */
    .pagination { display: flex; gap: 8px; justify-content: center; margin-top: 25px; }
    .page-link { 
        padding: 8px 15px; background: #fff; border: 1px solid #ddd; 
        color: #333; text-decoration: none; border-radius: 4px; font-weight: bold; transition: 0.3s;
    }
    .page-link:hover { background: #f4f6f9; border-color: #ff3333; color: #ff3333; }
    .page-link.active { background: #ff3333; color: #fff; border-color: #ff3333; }
    
    /* Căn chỉnh bộ lọc */
    .filter-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .filter-form { display: flex; gap: 10px; align-items: center; }
</style>

<div class="main-content">
    <h2>Quản lý Sản phẩm</h2>
    
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <input type="text" name="keyword" class="form-control" placeholder="Tên sản phẩm..." value="<?= htmlspecialchars($keyword) ?>" style="width: 250px; padding: 8px;">
            
            <select name="category_id" class="form-control" style="width: 200px; padding: 8px;">
                <option value="">-- Tất cả danh mục --</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat_id == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-edit" style="margin: 0;"><i class="fas fa-search"></i> Lọc</button>
            <a href="index.php" class="btn" style="background: #6c757d; margin: 0;"><i class="fas fa-sync-alt"></i> Hủy</a>
        </form>

        <a href="create.php" class="btn btn-add" style="margin: 0;"><i class="fas fa-plus"></i> Thêm sản phẩm</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Nhà cung cấp</th>
                <th>Giá (VNĐ)</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($products)): ?>
                <tr><td colspan="8" style="text-align:center; padding: 30px; color: #888;">Không tìm thấy sản phẩm nào phù hợp!</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td><?= htmlspecialchars($p['category_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($p['supplier_name'] ?? 'N/A') ?></td>
                    <td style="color: #ff3333; font-weight: bold;"><?= number_format($p['price'], 0, ',', '.') ?>đ</td>
                    <td><?= $p['stock_quantity'] ?></td>
                    <td>
                        <?php if($p['is_active']): ?>
                            <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">Đang bán</span>
                        <?php else: ?>
                            <span style="background: #ffebee; color: #c62828; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">Ngừng bán</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                        <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php 
            // Nút "Trang trước"
            if ($page > 1): 
                $prev = $page - 1;
            ?>
                <a href="?page=<?= $prev ?>&keyword=<?= urlencode($keyword) ?>&category_id=<?= $cat_id ?>" class="page-link">&laquo;</a>
            <?php endif; ?>

            <?php 
            // In các số trang
            for($i = 1; $i <= $total_pages; $i++): 
            ?>
                <a href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>&category_id=<?= $cat_id ?>" 
                   class="page-link <?= $i == $page ? 'active' : '' ?>">
                   <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php 
            // Nút "Trang tiếp"
            if ($page < $total_pages): 
                $next = $page + 1;
            ?>
                <a href="?page=<?= $next ?>&keyword=<?= urlencode($keyword) ?>&category_id=<?= $cat_id ?>" class="page-link">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 15px; color: #888; font-size: 13px;">
        Hiển thị trang <?= $page ?> / <?= $total_pages ?> (Tổng cộng <?= $total_items ?> sản phẩm)
    </div>
</div>

<?php require_once '../../admin/includes/footer.php'; ?>