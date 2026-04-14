<?php
require_once '../../admin/includes/header.php';
require_once '../../models/article_model.php';

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    die("<div style='margin: 20px;'><h2>Lỗi: Không tìm thấy ID bài viết.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

$id = $_GET['id'];
$article = getArticleById($id);

if (!$article) {
    die("<div style='margin: 20px;'><h2>Lỗi: Bài viết không tồn tại.</h2><a href='index.php' class='btn'>Quay lại</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'title' => trim($_POST['title']),
        'content' => $_POST['content'], 
        'author_id' => $article['author_id'] // Giữ nguyên tác giả cũ
    ];

    if (empty($data['title']) || empty($data['content'])) {
        $error = "Vui lòng nhập đầy đủ Tiêu đề và Nội dung!";
    } else {
        try {
            updateArticle($id, $data);
            $success = "Cập nhật bài viết thành công!";
            // Cập nhật lại dữ liệu hiển thị
            $article['title'] = $data['title'];
            $article['content'] = $data['content'];
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<style>
    .ck-editor__editable_inline { min-height: 400px; }
</style>

<div style="margin: 20px;">
    <h2>Sửa bài viết</h2>
    <a href="index.php" class="btn" style="background-color: #6c757d; margin-bottom: 15px;">Quay lại danh sách</a>
    
    <?php if ($error): ?>
        <div style="color: red; padding: 10px; margin-bottom: 15px; border: 1px solid red;"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color: green; padding: 10px; margin-bottom: 15px; border: 1px solid green;"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Tiêu đề bài viết:</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($article['title']) ?>" required style="width: 100%; padding: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Nội dung bài viết:</label>
            <textarea name="content" id="editor"><?= $article['content'] ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-edit" style="padding: 10px 20px; font-size: 16px;">Lưu thay đổi</button>
    </form>
</div>

<script>
    ClassicEditor
        .create( document.querySelector( '#editor' ), {
            toolbar: [ 
                'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                'insertImage', 'mediaEmbed', 'undo', 'redo' 
            ]
        })
        .catch( error => { console.error( error ); } );
</script>

<?php require_once '../../admin/includes/footer.php'; ?>