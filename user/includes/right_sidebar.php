<?php
// Gọi model bài viết
require_once __DIR__ . '/../../models/article_model.php';
$articles = getAllArticles();

// Cắt lấy 2 bài viết mới nhất (vị trí 0, lấy 2 phần tử)
$recent_articles = array_slice($articles, 0, 2);
?>

<aside class="character-guide-container">
    <div style="border-bottom: 2px solid #ff3333; padding-bottom: 10px; margin-bottom: 20px;">
        <h3 style="font-size: 16px; text-transform: uppercase;">Tin Tức & Sự Kiện</h3>
    </div>

    <?php if(empty($recent_articles)): ?>
        <p style="font-size: 13px; color: #666; text-align: center;">Chưa có tin tức nào.</p>
    <?php else: ?>
        <?php foreach($recent_articles as $article): ?>
        <div class="speech-bubble" style="margin-bottom: 15px;">
            <h4 style="color: #ff3333; margin-bottom: 5px; font-size: 14px;"><?= htmlspecialchars($article['title']) ?></h4>
            
            <div class="bubble-text" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                <?= strip_tags($article['content']) ?>
            </div>
            
            <a href="<?= BASE_URL ?>/user/article.php?id=<?= $article['id'] ?>" class="chat-link">
                Xem chi tiết <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="character-image" style="margin-top: 20px;">
        <img src="<?= BASE_URL ?>/assets/images/guide-character.png" alt="Guide Character" onerror="this.src='https://via.placeholder.com/300x400?text=Nhân+vật+UmaCT'">
    </div>
</aside>