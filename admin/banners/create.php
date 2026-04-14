<?php
require_once '../../admin/includes/header.php';
require_once '../../models/banner_model.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'image_url' => $_POST['image_url'] ?? '',
        'link' => trim($_POST['link']),
        'position' => trim($_POST['position'])
    ];

    if (empty($data['image_url'])) {
        $error = "Bạn phải tải lên 1 tấm ảnh cho Banner!";
    } else {
        try {
            addBanner($data);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<style>
    .upload-box { border: 2px dashed #28a745; padding: 30px; text-align: center; border-radius: 8px; cursor: pointer; background: #f8f9fa; }
    .upload-box:hover { background: #e2e6ea; }
    #image-preview { margin-top: 15px; position: relative; display: inline-block; }
    #image-preview img { max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .loading { position: absolute; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; font-weight: bold; }
</style>

<div style="margin: 20px;">
    <h2>Thêm Banner mới</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại</a>
    
    <?php if ($error): ?><div style="color: red; padding: 10px; margin-bottom: 15px; background: #f8d7da;"><?= $error ?></div><?php endif; ?>

    <form action="" method="POST" style="max-width: 600px;">
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Hình ảnh Banner:</label>
            <label class="upload-box" style="display: block;">
                🖼️ Click để tải ảnh lên (Sẽ tự động nén & up lên Cloudinary)
                <input type="file" id="fileInput" accept="image/*" style="display: none;">
            </label>
            
            <div id="image-preview"></div>
            <input type="hidden" name="image_url" id="hidden_image_url" value="">
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Đường dẫn khi click (Link):</label>
            <input type="text" name="link" class="form-control" style="width: 100%; padding: 8px;" placeholder="VD: /products?category=anime">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Vị trí hiển thị:</label>
            <select name="position" class="form-control" style="width: 100%; padding: 8px;">
                <option value="Trang chủ - Slider Top">Trang chủ - Slider Top</option>
                <option value="Trang chủ - Cột bên">Trang chủ - Cột bên</option>
                <option value="Danh mục sản phẩm">Danh mục sản phẩm</option>
            </select>
        </div>

        <button type="submit" class="btn btn-add" style="padding: 10px 20px;">Lưu Banner</button>
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
        fileInput.value = ''; 
    });

    async function processAndUpload(file) {
        // Ghi đè UI hiển thị chữ Đang tải
        previewArea.innerHTML = `<div style="width:100%; height:150px; background:#eee; position:relative;"><div class="loading">Đang tải ảnh lên Cloudinary...</div></div>`;

        // Resize lớn hơn sản phẩm một chút vì banner cần nét (max 1200px)
        const compressedBlob = await compressImage(file, 1200, 0.85);
        const formData = new FormData();
        formData.append('image', compressedBlob, file.name);

        try {
            const res = await fetch('../../utils/ajax_upload.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                // Hiển thị ảnh và lưu link vào thẻ input
                previewArea.innerHTML = `<img src="${data.url}">`;
                hiddenInput.value = data.url;
            } else {
                alert('Lỗi: ' + data.message); previewArea.innerHTML = '';
            }
        } catch (err) {
            alert('Lỗi kết nối!'); previewArea.innerHTML = '';
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