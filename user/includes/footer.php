</main> 
<?php 
  $hide_side_pages = ['cart.php', 'checkout.php'];
  $is_expanded = in_array(basename($_SERVER['PHP_SELF']), $hide_side_pages);
?>
<footer class="<?= $is_expanded ? 'expanded-mode' : '' ?>">
    <div class="footer-content-grid">
        <div class="footer-section">
            <h3>Liên hệ UmaCT</h3>
            <div class="footer-info">
                <i class="fas fa-map-marker-alt"></i>
                <span>Khoa CNTT & Chuyển đổi số, Đại học Nguyễn Trãi, Hà Nội</span>
            </div>
            <div class="footer-info">
                <i class="fas fa-phone"></i>
                <span>0123.456.789 (Hỗ trợ 24/7)</span>
            </div>
            <div class="footer-info">
                <i class="fas fa-envelope"></i>
                <span>support@umact.vn</span>
            </div>
        </div>

        <div class="footer-section">
            <h3>Chính sách & Hỗ trợ</h3>
            <div class="footer-links">
                <a href="#"><i class="fas fa-angle-right" style="margin-right: 5px; font-size:12px;"></i> Hướng dẫn mua hàng</a>
                <a href="#"><i class="fas fa-angle-right" style="margin-right: 5px; font-size:12px;"></i> Chính sách bảo hành</a>
                <a href="#"><i class="fas fa-angle-right" style="margin-right: 5px; font-size:12px;"></i> Chính sách đổi trả</a>
                <a href="#"><i class="fas fa-angle-right" style="margin-right: 5px; font-size:12px;"></i> Bảo mật thông tin</a>
            </div>
        </div>
    </div>

    <div class="copyright-area">
        <p>Bản quyền © 2026 <span class="brand">UmaCT</span>. Phát triển bởi <span class="dev">Nguyễn Công Tùng</span>.</p>
    </div>
</footer>
<style>
#toast-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 15px;
    pointer-events: none; /* Không cho click xuyên qua khối vô hình này */
}

.umact-toast {
    min-width: 280px;
    background-color: #fff;
    color: #333;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 600;
    pointer-events: auto; /* Cho phép click vào chính cái toast */
    /* Giấu nó ra ngoài màn hình bên phải */
    transform: translateX(120%);
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.umact-toast.show {
    transform: translateX(0);
    opacity: 1;
}

.umact-toast.success { border-left: 4px solid #27ae60; }
.umact-toast.success i { color: #27ae60; font-size: 18px; }

.umact-toast.error { border-left: 4px solid #e74c3c; }
.umact-toast.error i { color: #e74c3c; font-size: 18px; }
.main-content.expanded-mode, 
footer.expanded-mode {
    margin-right: 30px !important;
    max-width: 100%;
}
</style>

<div id="toast-container"></div>

<script>
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `umact-toast ${type}`;

    const icon = type === 'success' 
        ? '<i class="fas fa-check-circle"></i>' 
        : '<i class="fas fa-exclamation-circle"></i>';

    toast.innerHTML = `${icon} <span>${message}</span>`;
    container.appendChild(toast);

    // Kích hoạt animation
    setTimeout(() => { toast.classList.add('show'); }, 10);

    // Thu hồi sau 3s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => { toast.remove(); }, 400);
    }, 3000);
}
</script>
</body>
</html>