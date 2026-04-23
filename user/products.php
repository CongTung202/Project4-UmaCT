<?php 
require_once 'includes/header.php'; 
require_once '../models/product_model.php';
require_once '../models/category_model.php';

// Gọi CSS
echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/home.css">';
echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/products.css">';

// ==========================================
// 1. NHẬN THAM SỐ TỪ URL (LỌC & TÌM KIẾM)
// ==========================================
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float)$_GET['min_price'] : null;
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float)$_GET['max_price'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$limit = 12; // Số sản phẩm hiển thị trên 1 trang

// Lấy danh sách Categories để hiển thị Tabs
$categories = getAllCategories();

// ==========================================
// 2. LẤY & LỌC DỮ LIỆU
// ==========================================
if ($keyword !== '') {
    $products = searchProducts($keyword);
} else {
    // Chỉ lấy sản phẩm đang bán (is_active == 1)
    $products = array_filter(getAllProducts(), fn($p) => $p['is_active'] == 1);
}

// 2.1. Lọc theo Danh mục (Tab)
if ($category_id > 0) {
    $products = array_filter($products, fn($p) => $p['category_id'] == $category_id);
}

// 2.2. Lọc theo Khoảng Giá
if ($min_price !== null) {
    $products = array_filter($products, fn($p) => $p['price'] >= $min_price);
}
if ($max_price !== null) {
    $products = array_filter($products, fn($p) => $p['price'] <= $max_price);
}

// ==========================================
// 3. SẮP XẾP DỮ LIỆU (USORT)
// ==========================================
if ($sort === 'asc') {
    // Giá Thấp -> Cao
    usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($sort === 'desc') {
    // Giá Cao -> Thấp
    usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
} else {
    // Mặc định (Mới nhất)
    usort($products, fn($a, $b) => $b['id'] <=> $a['id']);
}

// ==========================================
// 4. PHÂN TRANG (PAGINATION)
// ==========================================
$total_products = count($products);
$total_pages = ceil($total_products / $limit);
if ($page < 1) $page = 1;
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

// Cắt mảng để lấy đúng số sản phẩm của trang hiện tại
$offset = ($page - 1) * $limit;
$paginated_products = array_slice($products, $offset, $limit);

// Hàm tạo URL giữ nguyên các tham số lọc hiện tại khi chuyển trang hoặc đổi tab
function buildFilterUrl($updates = []) {
    $params = $_GET;
    foreach ($updates as $key => $value) {
        if ($value === null) {
            unset($params[$key]); // THÊM DÒNG NÀY: Xóa tham số nếu giá trị truyền vào là null
        } else {
            $params[$key] = $value;
        }
    }
    // Nếu đổi category, tìm kiếm, giá hoặc sắp xếp thì reset về trang 1
    if (isset($updates['category']) || isset($updates['keyword']) || isset($updates['sort']) || isset($updates['min_price']) || array_key_exists('min_price', $updates)) {
        $params['page'] = 1;
    }
    return '?' . http_build_query($params);
}
?>

<div class="category-section" style="background: #fff; padding: 20px; border-radius: 8px;">
    
    <div style="margin-bottom: 20px;">
        <?php if ($keyword !== ''): ?>
            <h2>🔎 Kết quả tìm kiếm cho: <span style="color: #ff3333;">"<?= htmlspecialchars($keyword) ?>"</span> (<?= $total_products ?> sản phẩm)</h2>
        <?php else: ?>
            <h2>Cửa hàng Mô hình</h2>
        <?php endif; ?>
    </div>

    <div class="category-tabs">
        <a href="<?= buildFilterUrl(['category' => 0]) ?>" class="cat-tab <?= $category_id == 0 ? 'active' : '' ?>">Tất cả sản phẩm</a>
        <?php foreach($categories as $cat): ?>
            <a href="<?= buildFilterUrl(['category' => $cat['id']]) ?>" class="cat-tab <?= $category_id == $cat['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form action="" method="GET" class="filter-bar">
        <?php if($keyword !== '') echo '<input type="hidden" name="keyword" value="'.htmlspecialchars($keyword).'">'; ?>
        <input type="hidden" name="category" value="<?= $category_id ?>">
        
        <div class="price-filter-group">
            <span style="font-weight: bold; font-size: 14px; color: #555;">Khoảng giá:</span>
            <input type="number" name="min_price" class="price-input" placeholder="Từ..." value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
            <span>-</span>
            <input type="number" name="max_price" class="price-input" placeholder="Đến..." value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
            <button type="submit" class="btn-filter">Lọc Giá</button>
            
            <?php if(isset($_GET['min_price']) || isset($_GET['max_price'])): ?>
                <a href="<?= buildFilterUrl(['min_price' => null, 'max_price' => null, 'page' => 1]) ?>" class="btn-clear-filter" title="Xóa khoảng giá">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="sort-group">
            <span style="font-weight: bold; font-size: 14px; color: #555; margin-right: 10px;">Sắp xếp:</span>
            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="default" <?= $sort == 'default' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="asc" <?= $sort == 'asc' ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                <option value="desc" <?= $sort == 'desc' ? 'selected' : '' ?>>Giá: Cao đến Thấp</option>
            </select>
        </div>
    </form>

    <div class="product-grid">
        <?php if(empty($paginated_products)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px 0;">
                <h3 style="color: #666;">Không tìm thấy sản phẩm nào phù hợp với bộ lọc!</h3>
                <a href="products.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #ff3333; color: white; text-decoration: none; border-radius: 6px;">Xóa bộ lọc</a>
            </div>
        <?php else: ?>
            <?php foreach($paginated_products as $p): ?>
            <div class="product-item">
                <a href="detail.php?id=<?= $p['id'] ?>" class="img-wrapper">
                    <img src="<?= $p['main_image'] ?? 'https://via.placeholder.com/240x240?text=No+Image' ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                </a>
                <div class="info">
                    <a href="detail.php?id=<?= $p['id'] ?>" style="text-decoration: none;">
                        <div class="name"><?= htmlspecialchars($p['name']) ?></div>
                    </a>
                    <div class="price"><?= number_format($p['price'], 0, ',', '.') ?> đ</div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="pagination">
        <?php if($page > 1): ?>
            <a href="<?= buildFilterUrl(['page' => $page - 1]) ?>" class="page-item"><i class="fas fa-angle-left"></i></a>
        <?php else: ?>
            <span class="page-item disabled"><i class="fas fa-angle-left"></i></span>
        <?php endif; ?>

        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="<?= buildFilterUrl(['page' => $i]) ?>" class="page-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if($page < $total_pages): ?>
            <a href="<?= buildFilterUrl(['page' => $page + 1]) ?>" class="page-item"><i class="fas fa-angle-right"></i></a>
        <?php else: ?>
            <span class="page-item disabled"><i class="fas fa-angle-right"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>