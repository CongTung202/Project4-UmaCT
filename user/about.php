<?php require_once 'includes/header.php'; ?>

<style>
    /* CSS dành riêng cho trang Giới thiệu */
    .about-wrapper {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        line-height: 1.6;
        color: #333;
    }
    .about-title {
        font-size: 28px;
        color: #ff3333;
        text-align: center;
        margin-bottom: 25px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .about-banner {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 35px;
    }
    .about-section {
        margin-bottom: 35px;
    }
    .about-section h3 {
        font-size: 20px;
        color: #222;
        border-bottom: 2px solid #ff3333;
        padding-bottom: 8px;
        margin-bottom: 15px;
        display: inline-block;
    }
    .about-section p {
        font-size: 15px;
        color: #444;
        margin-bottom: 10px;
    }
    
    /* Lưới các ô Điểm mạnh */
    .about-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-top: 40px;
    }
    .feature-box {
        text-align: center;
        padding: 25px 20px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 1px solid #eee;
        transition: 0.3s;
    }
    .feature-box:hover {
        border-color: #ff3333;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(255,51,51,0.1);
    }
    .feature-box i {
        font-size: 40px;
        color: #ff3333;
        margin-bottom: 15px;
    }
    .feature-box h4 {
        font-size: 16px;
        margin-bottom: 10px;
        color: #111;
        font-weight: bold;
    }
    .feature-box p {
        font-size: 13px;
        color: #666;
        margin: 0;
    }
</style>

<div class="">
    <div class="about-wrapper">
        <img src="<?= BASE_URL ?>/assets/images/2.png" alt="UmaCT Banner" class="about-banner" onerror="this.src='https://placehold.co/1000x300/f5f5f5/ff3333?text=UmaCT+-+The+World+of+Anime'">

        <h1 class="about-title">Chào mừng đến với UmaCT</h1>

        <div class="about-section">
            <h3><i class="fas fa-store"></i> Câu chuyện của chúng tôi</h3>
            <p>Khởi nguồn từ niềm đam mê mãnh liệt với văn hóa Anime/Manga và khát khao mang đến một sân chơi chất lượng cho cộng đồng yêu thích 2D tại Việt Nam, <strong>UmaCT</strong> đã chính thức ra đời. Chúng tôi không chỉ là một cửa hàng, mà còn là nơi giao lưu, chia sẻ và lan tỏa tình yêu với các nhân vật mà bạn hâm mộ.</p>
        </div>

        <div class="about-section">
            <h3><i class="fas fa-box-open"></i> Chúng tôi cung cấp những gì?</h3>
            <p>UmaCT tự hào là điểm đến tin cậy cung cấp đa dạng các sản phẩm chất lượng, bao gồm:</p>
            <ul style="margin-left: 20px; margin-top: 10px; list-style-type: square; color: #555; line-height: 1.8;">
                <li><strong>Mô hình / Figure:</strong> Từ Nendoroid đáng yêu, Figma linh hoạt cho đến Scale Figure sắc nét từ các nhà sản xuất danh tiếng như Good Smile Company, Cygames Store...</li>
                <li><strong>Trang phục / Cosplay:</strong> Các set đồ cosplay chuẩn form, chi tiết tỉ mỉ giúp bạn hóa thân hoàn hảo thành waifu/husbando của lòng mình.</li>
                <li><strong>Phụ kiện Anime:</strong> Móc khóa acrylic, huy hiệu, poster và hàng ngàn món đồ lưu niệm độc đáo khác để bạn thỏa sức trang trí góc học tập.</li>
            </ul>
        </div>

        <div class="about-features">
            <div class="feature-box">
                <i class="fas fa-gem"></i>
                <h4>Chất lượng đảm bảo</h4>
                <p>Cam kết 100% hàng chuẩn, đóng gói nguyên seal, nhập khẩu trực tiếp từ các đối tác uy tín.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-shipping-fast"></i>
                <h4>Giao hàng tốc độ</h4>
                <p>Quy trình đóng gói chống sốc 3 lớp cực kỳ cẩn thận. Giao hàng nhanh chóng trên toàn quốc.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-headset"></i>
                <h4>Hỗ trợ tận tâm</h4>
                <p>Đội ngũ tư vấn viên nhiệt tình, sẵn sàng giải đáp mọi thắc mắc của bạn trước và sau khi mua hàng.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/right_sidebar.php'; ?>
<?php require_once 'includes/footer.php'; ?>