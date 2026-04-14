<?php
require_once '../../admin/includes/header.php';
require_once '../../models/article_model.php';

$articles = getAllArticles();
?>

<div style="margin: 20px;">
    <h2>Quản lý Bài viết / Tin tức</h2>
    <a href="create.php" class="btn btn-add">+ Viết bài mới</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Tác giả</th>
                <th>Ngày đăng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($articles)): ?>
                <tr><td colspan="5" style="text-align:center;">Chưa có bài viết nào.</td></tr>
            <?php else: ?>
                <?php foreach ($articles as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><strong><?= htmlspecialchars($a['title']) ?></strong></td>
                    <td><?= htmlspecialchars($a['author_name'] ?? $a['username']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $a['id'] ?>" class="btn btn-edit">Sửa</a>
                        <a href="delete.php?id=<?= $a['id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa bài viết này?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../admin/includes/footer.php'; ?>