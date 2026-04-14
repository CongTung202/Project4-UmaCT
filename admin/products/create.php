<?php
require_once '../../admin/includes/header.php';
require_once '../../models/category_model.php';
require_once '../../models/supplier_model.php';
require_once '../../models/product_model.php';

$categories = getAllCategories();
$suppliers = getAllSuppliers();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy mảng link ảnh do JS đẩy vào (Nếu không có thì để mảng rỗng)
    $uploaded_images = isset($_POST['uploaded_images']) ? $_POST['uploaded_images'] : [];
    
    $data = [
        'name' => trim($_POST['name']),
        'category_id' => (int)$_POST['category_id'],
        'supplier_id' => (int)$_POST['supplier_id'],
        'price' => (float)$_POST['price'],
        'stock_quantity' => (int)$_POST['stock_quantity'],
        'description' => trim($_POST['description']),
        'is_active' => isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true,
        'images' => json_encode($uploaded_images) // Ép mảng thành chuỗi JSON
    ];

    if (empty($data['name']) || empty($data['price'])) {
        $error = "Vui lòng nhập Tên sản phẩm và Giá!";
    } else {
        try {
            addProduct($data);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<style>
    .upload-box { border: 2px dashed #007bff; padding: 20px; text-align: center; border-radius: 8px; cursor: pointer; background: #f8f9fa; }
    .upload-box:hover { background: #e2e6ea; }
    #image-preview { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; }
    .img-box { position: relative; width: 100px; height: 100px; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
    .img-box img { width: 100%; height: 100%; object-fit: cover; }
    .remove-btn { position: absolute; top: 2px; right: 2px; background: red; color: white; border: none; cursor: pointer; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; line-height: 1; padding: 0; }
    .loading { position: absolute; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
</style>

<div style="margin: 20px;">
    <h2>Thêm Sản phẩm</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại</a>
    
    <?php if ($error): ?><div style="color: red; padding: 10px; margin-bottom: 15px; background: #f8d7da;"><?= $error ?></div><?php endif; ?>

    <form action="" method="POST" style="max-width: 600px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Tên sản phẩm:</label>
            <input type="text" name="name" class="form-control" required style="width: 100%; padding: 8px;">
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Danh mục:</label>
                <select name="category_id" class="form-control" style="width: 100%; padding: 8px;">
                    <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: bold;">Nhà cung cấp:</label>
                <select name="supplier_id" class="form-control" style="width: 100%; padding: 8px;">
                    <?php foreach ($suppliers as $sup): ?><option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold;">Giá bán (VNĐ):</label>
                <input type="number" name="price" class="form-control" required style="width: 100%; padding: 8px;">
            </div>
            <div style="flex: 1;">
                <label style="font-weight: bold;">Tồn kho:</label>
                <input type="number" name="stock_quantity" class="form-control" required style="width: 100%; padding: 8px;">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Thư viện ảnh:</label>
            <label class="upload-box" style="display: block;">
                📸 Click để chọn ảnh (Có thể chọn nhiều)
                <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
            </label>
            <div id="image-preview"></div>
            <div id="hidden-inputs"></div>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Trạng thái:</label>
            <select name="is_active" class="form-control" style="width: 100%; padding: 8px;">
                <option value="1">Đang bán</option>
                <option value="0">Ngừng bán</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Mô tả:</label>
            <textarea name="description" class="form-control" rows="4" style="width: 100%; padding: 8px;"></textarea>
        </div>
        
        <button type="submit" class="btn btn-add" style="padding: 10px 20px;">Lưu sản phẩm</button>
    </form>
</div>

<script>
    const fileInput = document.getElementById('fileInput');
    const previewArea = document.getElementById('image-preview');
    const hiddenInputsArea = document.getElementById('hidden-inputs');

    fileInput.addEventListener('change', async function(e) {
        const files = e.target.files;
        for (let i = 0; i < files.length; i++) processAndUpload(files[i]);
        fileInput.value = ''; 
    });

    async function processAndUpload(file) {
        const id = 'img_' + Math.random().toString(36).substr(2, 9);
        previewArea.insertAdjacentHTML('beforeend', `<div class="img-box" id="box_${id}"><div class="loading">Đang tải...</div></div>`);

        const compressedBlob = await compressImage(file, 800, 0.8);
        const formData = new FormData();
        formData.append('image', compressedBlob, file.name);

        try {
            // Nhớ đảm bảo file ajax_upload.php ở đúng đường dẫn này
            const res = await fetch('../../utils/ajax_upload.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                document.getElementById(`box_${id}`).innerHTML = `<img src="${data.url}"><button type="button" class="remove-btn" onclick="removeImage('${id}')">X</button>`;
                hiddenInputsArea.insertAdjacentHTML('beforeend', `<input type="hidden" name="uploaded_images[]" id="input_${id}" value="${data.url}">`);
            } else {
                alert('Lỗi: ' + data.message);
                document.getElementById(`box_${id}`).remove();
            }
        } catch (err) {
            alert('Lỗi kết nối!'); document.getElementById(`box_${id}`).remove();
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

    function removeImage(id) {
        document.getElementById(`box_${id}`).remove();
        document.getElementById(`input_${id}`).remove();
    }
</script>

<?php require_once '../../admin/includes/footer.php'; ?>