<?php 
require_once 'includes/header.php'; 
require_once '../models/article_model.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = getArticleById($id);

if (!$article) {
    die("<div class='main-content'><h2>Không tìm thấy bài viết!</h2></div>");
}
?>

<div >
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        
        <!-- Nút quay lại -->
        <a href="articles.php" style="text-decoration: none; color: #888; font-size: 14px; display: inline-block; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>

        <!-- Tiêu đề & Thông tin tác giả -->
        <h1 style="color: #333; margin-bottom: 15px; font-size: 26px; line-height: 1.4;">
            <?= htmlspecialchars($article['title']) ?>
        </h1>

        <!-- NỘI DUNG BÀI VIẾT CHÍNH (Hiển thị HTML trực tiếp) -->
        <div class="article-content" style="line-height: 1.8; color: #444; font-size: 15px;">
            <?= $article['content'] ?>
        </div>
        
    </div>
</div>

<style>
    /* CSS bảo vệ giao diện bài viết khỏi bị vỡ do chèn ảnh/video */
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 15px 0;
    }
    .article-content a {
        color: #3498db;
        text-decoration: none;
        font-weight: bold;
    }
    .article-content a:hover {
        color: #ff3333;
        text-decoration: underline;
    }
    .article-content p { margin-bottom: 15px; }
</style>

<script>
// Tái sử dụng logic chuyển <oembed> thành video youtube nếu bác chèn video vào bài viết
document.addEventListener("DOMContentLoaded", function() {
    const mediaElements = document.querySelectorAll('oembed[url]');
    mediaElements.forEach(el => {
        const url = el.getAttribute('url');
        let iframeSrc = '';
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            const videoId = url.split(/v\/|v=|youtu\.be\//)[1].split(/[?&]/)[0];
            iframeSrc = `https://www.youtube.com/embed/${videoId}`;
        }
        if (iframeSrc) {
            const iframe = document.createElement('iframe');
            iframe.setAttribute('src', iframeSrc);
            iframe.setAttribute('width', '100%');
            iframe.setAttribute('height', '450');
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
            iframe.setAttribute('allowfullscreen', 'true');
            iframe.style.borderRadius = '12px';
            iframe.style.margin = '20px 0';
            el.parentNode.replaceChild(iframe, el);
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>