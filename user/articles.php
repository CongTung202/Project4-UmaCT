<?php 
require_once 'includes/header.php'; 
require_once '../models/article_model.php';

$articles = getAllArticles();
?>

<div >
    <h2 style="margin-bottom: 25px; border-bottom: 2px solid #ff3333; padding-bottom: 10px; display: inline-block;">
        <i class="fas fa-newspaper"></i> Tin tức & Cập nhật
    </h2>

    <?php if (empty($articles)): ?>
        <p style="color: #888; text-align: center; padding: 50px;">Hiện tại chưa có bài viết nào.</p>
    <?php else: ?>
        <div style="display: grid; gap: 20px;">
            <?php foreach ($articles as $art): ?>
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: 0.3s; border-left: 4px solid #ff3333;">
                    <a href="article_detail.php?id=<?= $art['id'] ?>" style="text-decoration: none; color: #333;">
                        <h3 style="margin-bottom: 10px; transition: 0.3s;" onmouseover="this.style.color='#ff3333'" onmouseout="this.style.color='#333'">
                            <?= htmlspecialchars($art['title']) ?>
                        </h3>
                    </a>
                    <div style="color: #888; font-size: 13px;">
                        <i class="fas fa-user-edit"></i> Đăng bởi: <b><?= htmlspecialchars($art['author_name']) ?></b> 
                        <span style="margin: 0 10px;">|</span>
                        <i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($art['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>