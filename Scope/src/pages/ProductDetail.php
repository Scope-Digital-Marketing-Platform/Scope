<?php
$product_id = isset($_GET['id']) ? $_GET['id'] : null;
$json_file_path = __DIR__ . '/../../backend/database/product_data.json';
$product = null;

if ($product_id && file_exists($json_file_path)) {
    $products = json_decode(file_get_contents($json_file_path), true) ?: [];
    foreach ($products as $p) {
        if ($p['id'] === $product_id) {
            $product = $p;
            break;
        }
    }
}

if (!$product) {
    echo "<div style='color:white; text-align:center; padding: 50px;'><h2>Ürün bulunamadı veya kaldırılmış.</h2><a href='store' style='color:#f39c12;'>Mağazaya Dön</a></div>";
    return;
}

$is_my_product = (isset($_SESSION['user_id']) && isset($product['seller_id']) && $_SESSION['user_id'] === $product['seller_id']);

$users_db_path = __DIR__ . '/../../backend/database/users_data.json';
$publisher_username = $product['seller_username'] ?? 'BilinmeyenKullanici'; 
$is_owned = false; // Ürünü satın almış mı?
$has_reviewed = false; // Ürüne yorum yapmış mı?

if (file_exists($users_db_path)) {
    $users = json_decode(file_get_contents($users_db_path), true) ?: [];
    
    // Satıcı adını bul
    foreach ($users as $u) {
        if (isset($product['seller_id']) && $u['id'] === $product['seller_id']) {
            $publisher_username = $u['username'];
        }
        // Şu anki kullanıcı giriş yapmışsa satın alma durumunu kontrol et
        if (isset($_SESSION['user_id']) && $u['id'] === $_SESSION['user_id']) {
            if (isset($u['assets']) && in_array($product_id, $u['assets'])) {
                $is_owned = true;
            }
        }
    }
}

// Ürüne ait yorumlar
$reviews = $product['reviews'] ?? [];
if (isset($_SESSION['user_id'])) {
    foreach ($reviews as $rev) {
        if ($rev['user_id'] === $_SESSION['user_id']) {
            $has_reviewed = true;
            break;
        }
    }
}

// Görseller
$images = [];
if (!empty($product['cover_image'])) $images[] = $product['cover_image'];
if (!empty($product['gallery']) && is_array($product['gallery'])) $images = array_merge($images, $product['gallery']);
if (empty($images)) $images[] = 'assets/images/default.jpg';
?>

<style>
    .detail-container { display: flex; gap: 60px; max-width: 1200px; width: 100%; margin: 40px auto; padding: 40px; text-align: left; box-sizing: border-box; background: #16161a; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); transition: all 0.4s ease; }
    .detail-left { flex: 2; min-width: 0; }

    .slider-wrapper { position: relative; width: 100%; height: 450px; border-radius: 16px; background-color: #0d0d0f; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.05); transition: all 0.4s ease; }
    .slider-img { max-width: 100%; max-height: 100%; object-fit: contain; display: none; animation: fadeIn 0.5s ease; }
    .slider-img.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .slider-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); color: white; border: none; border-radius: 50%; width: 45px; height: 45px; font-size: 1.5rem; cursor: pointer; z-index: 10; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
    .slider-btn:hover { background: #f39c12; transform: translateY(-50%) scale(1.1); }
    .slider-btn.prev { left: 15px; }
    .slider-btn.next { right: 15px; }
    .slider-dots { position: absolute; bottom: 20px; width: 100%; text-align: center; }
    .dot { display: inline-block; width: 10px; height: 10px; background: rgba(255,255,255,0.3); border-radius: 50%; margin: 0 6px; cursor: pointer; transition: 0.3s; }
    .dot.active { background: #f39c12; transform: scale(1.3); }

    .tab-header { display: flex; gap: 30px; margin-top: 40px; border-bottom: 2px solid rgba(255, 255, 255, 0.05); }
    .tab-btn { background: none; border: none; color: #8b949e; font-size: 1.1rem; font-weight: 500; cursor: pointer; padding-bottom: 15px; position: relative; transition: color 0.3s; }
    .tab-btn:hover { color: white; }
    .tab-btn.active { color: #f39c12; font-weight: bold; }
    .tab-btn.active::after { content: ''; position: absolute; left: 0; bottom: -2px; width: 100%; height: 2px; background: #f39c12; border-radius: 2px; }
    .tab-content { display: none; padding: 30px 0; color: #cbd5e1; line-height: 1.7; font-size: 1.05rem; animation: fadeIn 0.4s ease; }
    .tab-content.active { display: block; }

    /* YORUMLAR (REVIEWS) KISMI CSS */
    .reviews-list { display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; }
    .review-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; }
    .review-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .review-header strong { color: white; }
    .review-header .stars { color: #f39c12; }
    .review-header .date { color: #8b949e; font-size: 0.85rem; margin-left: auto; }
    .review-body { color: #cbd5e1; font-size: 0.95rem; word-break: break-word; }
    
    .review-form-box { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 8px; border: 1px dashed rgba(255,255,255,0.1); margin-top: 20px;}
    .review-form-box h3 { color: white; margin-top: 0; margin-bottom: 15px; font-size: 1.1rem; }
    .rating-select { margin-bottom: 15px; }
    .rating-select select { background: #1a1a1e; color: white; border: 1px solid #333; padding: 10px; border-radius: 6px; outline: none; width: 100%; cursor: pointer;}
    .review-form-box textarea { background: #1a1a1e; color: white; border: 1px solid #333; padding: 10px; border-radius: 6px; outline: none; width: 100%; box-sizing: border-box; resize: vertical; margin-bottom: 15px; }
    .review-form-box textarea:focus, .rating-select select:focus { border-color: #f39c12; }
    .submit-review-btn { background: #f39c12; color: #111; border: none; padding: 10px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: 0.3s; }
    .submit-review-btn:hover { opacity: 0.9; }
    .review-notice { color: #8b949e; font-style: italic; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 6px; text-align: center; border: 1px solid rgba(255,255,255,0.05); }

    /* SAĞ KOLON (Ürün Bilgileri) */
    .detail-right { flex: 1; min-width: 320px; }
    .product-title { font-size: 2.4rem; color: white; margin: 0 0 20px 0; font-weight: 700; transition: color 0.4s ease; }
    .publisher-box { display: inline-flex; align-items: center; gap: 15px; text-decoration: none; color: #cbd5e1; margin-bottom: 35px; padding: 10px 15px; border-radius: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; }
    .publisher-box:hover { background: rgba(243, 156, 18, 0.1); border-color: #f39c12; color: white; transform: translateY(-2px); }
    .publisher-avatar { width: 45px; height: 45px; border-radius: 50%; background-color: #333; overflow: hidden; }
    .publisher-name { font-size: 1.05rem; font-weight: 500; }
    .sales-info { border-top: 1px dashed rgba(255,255,255,0.1); border-bottom: 1px dashed rgba(255,255,255,0.1); padding: 20px 0; color: #8b949e; font-size: 1rem; margin-bottom: 35px; display: flex; align-items: center; gap: 10px; transition: all 0.4s ease; }
    .sales-info svg { width: 20px; fill: currentColor; }
    .product-price { font-size: 3.5rem; color: white; font-weight: 800; margin-bottom: 35px; transition: color 0.4s ease; }
    
    .add-to-cart-btn { width: 100%; background: #f39c12; color: #111; border: none; padding: 18px; font-size: 1.2rem; font-weight: 800; border-radius: 12px; cursor: pointer; transition: all 0.3s; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3); }
    .add-to-cart-btn:hover { background: #f1c40f; transform: translateY(-3px); box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4); }

    .edit-product-btn { display: block; text-align: center; width: 100%; background: #9b59b6; color: white; border: none; padding: 18px; font-size: 1.2rem; font-weight: 800; border-radius: 12px; cursor: pointer; transition: all 0.3s; text-transform: uppercase; letter-spacing: 1px; text-decoration: none; box-shadow: 0 4px 15px rgba(155, 89, 182, 0.3); box-sizing: border-box;}
    .edit-product-btn:hover { background: #8e44ad; color: white; transform: translateY(-3px); box-shadow: 0 6px 20px rgba(155, 89, 182, 0.4); }

    /* LİGHT MODE (AÇIK TEMA) AYARLARI */
    body.light-mode .detail-container { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06); }
    body.light-mode .product-title { color: #0f172a; }
    body.light-mode .slider-wrapper { background: #f8fafc; border: 1px solid #e2e8f0; }
    body.light-mode .slider-btn { background: rgba(255,255,255,0.8); color: #0f172a; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    body.light-mode .slider-btn:hover { background: #2563eb; color: white; }
    body.light-mode .dot { background: #cbd5e1; }
    body.light-mode .dot.active { background: #2563eb; }
    body.light-mode .tab-header { border-bottom: 2px solid #e2e8f0; }
    body.light-mode .tab-btn { color: #64748b; }
    body.light-mode .tab-btn:hover { color: #0f172a; }
    body.light-mode .tab-btn.active { color: #2563eb; }
    body.light-mode .tab-btn.active::after { background: #2563eb; }
    body.light-mode .tab-content { color: #334155; }
    body.light-mode .publisher-box { color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; }
    body.light-mode .publisher-box:hover { background: rgba(37, 99, 235, 0.05); border-color: #2563eb; color: #2563eb; }
    body.light-mode .sales-info { color: #64748b; border-color: #e2e8f0; }
    body.light-mode .product-price { color: #0f172a; }
    body.light-mode .add-to-cart-btn { background: #2563eb; color: white; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3); }
    body.light-mode .add-to-cart-btn:hover { background: #1d4ed8; box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4); }
    body.light-mode .edit-product-btn { background: #8b5cf6; color: white; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3); }
    body.light-mode .edit-product-btn:hover { background: #7c3aed; box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4); }
    
    /* Yorumlar Light Mode Overrides */
    body.light-mode .review-card { background: #f8fafc; border-color: #e2e8f0; }
    body.light-mode .review-header strong { color: #0f172a; }
    body.light-mode .review-body { color: #475569; }
    body.light-mode .review-form-box { background: #ffffff; border-color: #cbd5e1; }
    body.light-mode .review-form-box h3 { color: #0f172a; }
    body.light-mode .rating-select select, body.light-mode .review-form-box textarea { background: #f8fafc; color: #1e293b; border-color: #cbd5e1; }
    body.light-mode .rating-select select:focus, body.light-mode .review-form-box textarea:focus { border-color: #3b82f6; }
    body.light-mode .submit-review-btn { background: #3b82f6; color: white; }
    body.light-mode .submit-review-btn:hover { background: #2563eb; }
    body.light-mode .review-notice { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }

    @media (max-width: 900px) { .detail-container { flex-direction: column; padding: 20px; } .slider-wrapper { height: 350px; } }
</style>

<div class="detail-container">
    <div class="detail-left">
        <div class="slider-wrapper">
            <button class="slider-btn prev" onclick="changeSlide(-1)">&#10094;</button>
            
            <?php foreach($images as $index => $img): ?>
                <img src="<?php echo htmlspecialchars($img); ?>" class="slider-img <?php echo $index === 0 ? 'active' : ''; ?>" alt="Ürün Görseli">
            <?php endforeach; ?>
            
            <button class="slider-btn next" onclick="changeSlide(1)">&#10095;</button>
            
            <div class="slider-dots">
                <?php foreach($images as $index => $img): ?>
                    <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $index; ?>)"></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-header">
            <button class="tab-btn active" onclick="openTab('detay')">Ürün Detayları</button>
            <button class="tab-btn" onclick="openTab('yorumlar')">Yorumlar (<?php echo count($reviews); ?>)</button>
        </div>

        <div id="detay" class="tab-content active">
            <p><strong>Piyasaya Sürülme:</strong> <?php echo htmlspecialchars($product['created_at'] ?? 'Bilinmiyor'); ?></p>
            <p><strong>Varlık Türü:</strong> <?php echo htmlspecialchars($product['type'] ?? 'Belirtilmemiş'); ?></p>
            <br>
            <p><strong>Açıklama:</strong><br> <?php echo nl2br(htmlspecialchars($product['description'] ?? 'Bu ürün için henüz bir açıklama girilmemiş.')); ?></p>
        </div>

        <div id="yorumlar" class="tab-content">
            <?php if (empty($reviews)): ?>
                <p style="color: #8b949e;">Bu ürün için henüz yorum yapılmamış.</p>
            <?php else: ?>
                <div class="reviews-list">
                    <?php foreach($reviews as $rev): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <strong><?php echo htmlspecialchars($rev['username']); ?></strong>
                                <span class="stars"><?php echo str_repeat('⭐', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?></span>
                                <span class="date"><?php echo htmlspecialchars($rev['date']); ?></span>
                            </div>
                            <div class="review-body">
                                <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['is_logged_in'])): ?>
                <div class="review-notice">Yorum yapabilmek için giriş yapmalısınız.</div>
            <?php elseif ($is_my_product): ?>
                <div class="review-notice">Kendi ürününüze yorum yapamazsınız.</div>
            <?php elseif (!$is_owned): ?>
                <div class="review-notice">Yorum yapabilmek için bu ürünü satın almanız gerekmektedir.</div>
            <?php elseif ($has_reviewed): ?>
                <div class="review-notice">Bu ürün için zaten bir değerlendirme yaptınız. Desteğiniz için teşekkürler!</div>
            <?php else: ?>
                <div class="review-form-box">
                    <h3>Değerlendirmenizi Yazın</h3>
                    <div class="rating-select">
                        <select id="reviewRating">
                            <option value="5">5 Yıldız - Harika</option>
                            <option value="4">4 Yıldız - Çok İyi</option>
                            <option value="3">3 Yıldız - Ortalama</option>
                            <option value="2">2 Yıldız - Kötü</option>
                            <option value="1">1 Yıldız - Berbat</option>
                        </select>
                    </div>
                    <textarea id="reviewComment" rows="4" placeholder="Ürün hakkındaki deneyimleriniz..."></textarea>
                    <button class="submit-review-btn" onclick="submitReview('<?php echo htmlspecialchars($product_id); ?>')">Yorumu Gönder</button>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="detail-right">
        <h1 class="product-title"><?php echo htmlspecialchars($product['name'] ?? 'İsimsiz Ürün'); ?></h1>
        
        <a href="profile?user=<?php echo urlencode($publisher_username); ?>" class="publisher-box">
            <div class="publisher-avatar">
                <img src="assets/images/default_avatar.png" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">
            </div>
            <div class="publisher-name">Yayıncı / <?php echo htmlspecialchars($publisher_username); ?></div>
        </a>

        <div class="sales-info">
            <svg viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4H5v11h14V11h-3zm-6-4a2 2 0 014 0v4h-4V7z"/></svg>
            <?php echo htmlspecialchars($product['units_sold'] ?? '0'); ?> Adet Satıldı
        </div>

        <div class="product-price">
            <?php echo htmlspecialchars($product['price_total'] ?? '0'); ?> $
        </div>

        <?php if($is_my_product): ?>
            <a href="editproduct?id=<?php echo urlencode($product['id']); ?>" class="edit-product-btn">Ürünü Düzenle</a>
        <?php else: ?>
            <button class="add-to-cart-btn" onclick="addToCart('<?php echo htmlspecialchars($product['id']); ?>')">Sepete Ekle</button>
        <?php endif; ?>
    </div>
</div>

<script>
    // Dinamik Base URL'yi JS içine taşıdık
    const backendUrl = "<?php echo str_replace('index.php', '', $_SERVER['SCRIPT_NAME']); ?>index.php";

    // --- YORUM GÖNDERME AJAX ---
    function submitReview(productId) {
        const rating = document.getElementById('reviewRating').value;
        const comment = document.getElementById('reviewComment').value.trim();

        if (!comment) {
            alert("Lütfen bir yorum yazın.");
            return;
        }

        const btn = document.querySelector('.submit-review-btn');
        btn.innerText = "Gönderiliyor...";
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'submit_review');
        formData.append('product_id', productId);
        formData.append('rating', rating);
        formData.append('comment', comment);

        fetch(backendUrl, { method: 'POST', body: formData })
        .then(async response => {
            const text = await response.text(); 
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error("Sunucudan Gelen Hatalı Yanıt:", text);
                throw new Error("Sunucu JSON yerine geçersiz bir yanıt döndürdü.");
            }
        })
        .then(data => {
            if (data.status === 'success') {
                alert("Yorumunuz başarıyla eklendi!");
                window.location.reload();
            } else {
                alert(data.message || "Bir hata oluştu.");
                btn.innerText = "Yorumu Gönder";
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Hata Detayı:', error);
            alert("Sistemsel bir hata oluştu. (Eğer sorun devam ediyorsa F12'ye basıp Konsol'daki kırmızı hatayı kontrol edin.)");
            btn.innerText = "Yorumu Gönder";
            btn.disabled = false;
        });
    }

    // --- SEPETE EKLEME ---
    function addToCart(productId) {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', productId);

        fetch(backendUrl, { method: 'POST', body: formData })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error("Sunucudan Gelen Hatalı Yanıt:", text);
                throw new Error("Sunucu JSON yerine geçersiz bir yanıt döndürdü.");
            }
        })
        .then(data => {
            if(data.status === 'success') {
                alert("Ürün sepete eklendi!");
                window.location.reload(); 
            } else {
                alert("Ürün eklenirken hata oluştu. Lütfen giriş yaptığınızdan emin olun.");
            }
        })
        .catch(error => {
            console.error('Hata Detayı:', error);
            alert("Sistemsel bir hata oluştu veya oturumunuz kapalı.");
        });
    }

    let slideIndex = 0;
    const slides = document.querySelectorAll('.slider-img');
    const dots = document.querySelectorAll('.dot');

    function showSlides(n) {
        if (n >= slides.length) { slideIndex = 0; }
        if (n < 0) { slideIndex = slides.length - 1; }
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        if (slides.length > 0) {
            slides[slideIndex].classList.add('active');
            dots[slideIndex].classList.add('active');
        }
    }

    function changeSlide(n) { slideIndex += n; showSlides(slideIndex); }
    function currentSlide(n) { slideIndex = n; showSlides(slideIndex); }

    function openTab(tabId) {
        const contents = document.querySelectorAll('.tab-content');
        const btns = document.querySelectorAll('.tab-btn');
        contents.forEach(c => c.classList.remove('active'));
        btns.forEach(b => b.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }
</script>