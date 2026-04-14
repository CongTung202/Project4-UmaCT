<?php
require_once '../../admin/includes/header.php';
require_once '../../models/banner_model.php';

if (!isset($_GET['id'])) {
    die("<div style='margin: 20px;'><h2>Lỗi: Không tìm thấy ID banner.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$id = $_GET['id'];
$banner = getBannerById($id);

if (!$banner) {
    die("<div style='margin: 20px;'><h2>Lỗi: Banner không tồn tại.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'image_url' => $_POST['image_url'] ?? '',
        'link' => trim($_POST['link']),
        'position' => trim($_POST['position'])
    ];

    if (empty($data['image_url'])) {
        $error = "Ảnh banner không được để trống!";
    } else {
        try {
            updateBanner($id, $data);
            $success = "Cập nhật banner thành công!";
            $banner = array_merge($banner, $data); // Cập nhật lại biến để hiển thị
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<style>
    .upload-box { border: 2px dashed #007bff; padding: 20px; text-align: center; border-radius: 8px; cursor: pointer; background: #f8f9fa; }
    #image-preview { margin-top: 15px; position: relative; display: inline-block; width: 100%; max-width: 500px; }
    #image-preview img { width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .loading-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; font-weight: bold; border-radius: 8px; }
</style>

<div style="margin: 20px;">
    <h2>Sửa Banner #<?= $id ?></h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?><div style="color: red; padding: 10px; margin-bottom: 15px; background: #f8d7da; border-radius: 4px;"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div style="color: green; padding: 10px; margin-bottom: 15px; background: #d4edda; border-radius: 4px;"><?= $success ?></div><?php endif; ?>

    <form action="" method="POST" style="max-width: 700px;">
        <div class="form-group" style="margin-bottom: 20px;">
            <label style="font-weight: bold;">Hình ảnh hiện tại:</label>
            <label class="upload-box" style="display: block;">
                🔄 Click để thay đổi ảnh (Tự động nén & upload)
                <input type="file" id="fileInput" accept="image/*" style="display: none;">
            </label>
            
            <div id="image-preview">
                <img src="<?= htmlspecialchars($banner['image_url']) ?>" id="preview-img">
            </div>
            <input type="hidden" name="image_url" id="hidden_image_url" value="<?= htmlspecialchars($banner['image_url']) ?>">
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Đường dẫn (Link):</label>
            <input type="text" name="link" class="form-control" value="<?= htmlspecialchars($banner['link'] ?? '') ?>" style="width: 100%; padding: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Vị trí hiển thị:</label>
            <select name="position" class="form-control" style="width: 100%; padding: 8px;">
                <option value="Trang chủ - Slider Top" <?= ($banner['position'] == 'Trang chủ - Slider Top') ? 'selected' : '' ?>>Trang chủ - Slider Top</option>
                <option value="Trang chủ - Cột bên" <?= ($banner['position'] == 'Trang chủ - Cột bên') ? 'selected' : '' ?>>Trang chủ - Cột bên</option>
                <option value="Danh mục sản phẩm" <?= ($banner['position'] == 'Danh mục sản phẩm') ? 'selected' : '' ?>>Danh mục sản phẩm</option>
            </select>
        </div>

        <button type="submit" class="btn btn-edit" style="padding: 10px 25px; font-size: 16px;">Lưu thay đổi</button>
    </form>
</div>

<script>
    const fileInput = document.getElementById('fileInput');
    const previewArea = document.getElementById('image-preview');
    const hiddenInput = document.getElementById('hidden_image_url');

    fileInput.addEventListener('change', async function(e) {
        if (e.target.files.length > 0) {
            processAndUpload(e.target.files[0]);
        }
    });

    async function processAndUpload(file) {
        // Tạo lớp phủ loading
        const loader = document.createElement('div');
        loader.className = 'loading-overlay';
        loader.innerText = 'Đang xử lý ảnh...';
        previewArea.appendChild(loader);

        // Nén ảnh xuống tối đa 1200px (Chất lượng 0.8)
        const compressedBlob = await compressImage(file, 1200, 0.8);
        const formData = new FormData();
        formData.append('image', compressedBlob, file.name);

        try {
            const res = await fetch('../../utils/ajax_upload.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                document.getElementById('preview-img').src = data.url;
                hiddenInput.value = data.url;
                loader.remove();
            } else {
                alert('Lỗi: ' + data.message);
                loader.remove();
            }
        } catch (err) {
            alert('Lỗi kết nối server!');
            loader.remove();
        }
    }

    function compressImage(file, maxWidth, quality) {
        return new Promise(resolve => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = e => {
                const img = new Image();
                img.src = e.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let w = img.width, h = img.height;
                    if (w > maxWidth) { h = Math.round(h * maxWidth / w); w = maxWidth; }
                    canvas.width = w; canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    canvas.toBlob(blob => resolve(blob), 'image/jpeg', quality);
                };
            };
        });
    }
</script>

<?php require_once '../../admin/includes/footer.php'; ?>