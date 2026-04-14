<?php
require_once '../../admin/includes/header.php';
require_once '../../models/article_model.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'title' => trim($_POST['title']),
        // Lưu ý: Nội dung content từ CKEditor sẽ chứa các thẻ HTML (<b>, <i>, <p>...)
        // Nên ta không dùng htmlspecialchars hay trim quá mạnh tay ở đây
        'content' => $_POST['content'], 
        'author_id' => 1 // Tạm thời hardcode tác giả là Admin gốc (ID=1)
    ];

    if (empty($data['title']) || empty($data['content'])) {
        $error = "Vui lòng nhập Tiêu đề và Nội dung bài viết!";
    } else {
        try {
            addArticle($data);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<style>
    .ck-editor__editable_inline {
        min-height: 400px; /* Chiều cao mặc định của khung soạn thảo */
    }
</style>

<div style="margin: 20px;">
    <h2>Viết bài mới</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: red; padding: 10px; margin-bottom: 15px; border: 1px solid red;"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Tiêu đề bài viết:</label>
            <input type="text" name="title" class="form-control" required style="width: 100%; padding: 8px;" placeholder="Ví dụ: Đợt hàng Nendoroid tháng 12 đã cập bến!">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Nội dung bài viết:</label>
            <textarea name="content" id="editor"></textarea>
        </div>
        
        <button type="submit" class="btn btn-add" style="padding: 10px 20px; font-size: 16px;">Đăng bài</button>
    </form>
</div>

<script>
    ClassicEditor
        .create( document.querySelector( '#editor' ), {
            toolbar: [ 
                'heading', '|', 
                'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                'insertImage', /* Nút chèn ảnh bằng Link */
                'mediaEmbed',  /* Nút chèn Video Youtube */
                'undo', 'redo' /* Nút hoàn tác */
            ]
        })
        .catch( error => {
            console.error( error );
        } );
</script>

<?php require_once '../../admin/includes/footer.php'; ?>