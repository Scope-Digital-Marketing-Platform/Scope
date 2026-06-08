<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "<script>alert('Lütfen önce giriş yapın.'); window.location.href = 'home';</script>"; exit;
}

$product_id = isset($_GET['id']) ? $_GET['id'] : null;
$json_file_path = __DIR__ . '/../../backend/database/product_data.json';
$product = null;

if ($product_id && file_exists($json_file_path)) {
    $products = json_decode(file_get_contents($json_file_path), true) ?: [];
    foreach ($products as $p) {
        if ($p['id'] === $product_id && $p['seller_id'] === $_SESSION['user_id']) {
            $product = $p;
            break;
        }
    }
}

if (!$product) {
    echo "<div style='color:white; padding:50px;'><h2>Bu ürünü düzenleme yetkiniz yok veya ürün bulunamadı.</h2></div>";
    return;
}

$commission_rate = 0.03; 
// Mevcut dosya adı bilgisini alıyoruz
$current_file_name = !empty($product['product_file']) ? basename($product['product_file']) : 'Dosya Yok';
?>

<style>
    .cp-wrapper { display: flex; flex-direction: row; width: 100%; max-width: 1400px; margin: 40px auto; gap: 50px; padding: 0 20px; align-items: flex-start; }
    .cp-form-section { flex: 1; background: rgba(20, 20, 25, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 40px; min-height: 500px; position: relative; transition: all 0.4s ease; }
    .cp-form-section h2 { color: rgba(255, 255, 255, 0.2); text-align: center; font-size: 32px; margin-top: 0; margin-bottom: 40px; text-transform: uppercase; letter-spacing: 5px; }

    .cp-form-group { display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px; text-align: left; }
    .cp-form-group label { font-size: 14px; color: #8b949e; font-weight: 500; }
    .cp-input, .cp-select { background: #1a1a1e; border: 1px solid #333; border-radius: 8px; padding: 14px; color: white; outline: none; font-size: 14px; transition: all 0.3s; width: 100%; box-sizing: border-box; }
    .cp-input:focus, .cp-select:focus { border-color: #f39c12; }
    
    .cp-select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238b949e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 15px center; background-size: 16px; }

    .cp-image-upload-wrapper { display: flex; border: 2px dashed #333; border-radius: 8px; padding: 20px; gap: 20px; background: #151518; align-items: stretch; }
    .cp-main-image-box { flex: 0 0 200px; height: 200px; background: #1a1a1e; border: 1px solid #444; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; position: relative; overflow: hidden; text-align: center; padding: 10px; }
    .cp-main-image-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
    .cp-main-image-box svg { width: 40px; height: 40px; color: #8b949e; margin-bottom: 10px; }
    .cp-main-image-box span { color: #8b949e; font-size: 13px; font-weight: bold; }
    .cp-main-image-preview { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; z-index: 5; pointer-events: none; }

    /* YENİ: DOSYA YÜKLEME ALANI STİLİ */
    .cp-custom-file-wrapper { position: relative; background: #1a1a1e; border: 1px dashed #444; border-radius: 8px; padding: 20px; color: #8b949e; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; text-align: center; }
    .cp-custom-file-wrapper:hover { border-color: #f39c12; }
    .cp-custom-file-wrapper input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0 !important; cursor: pointer; z-index: 10; }

    .cp-price-row { display: flex; gap: 20px; margin-bottom: 10px; }
    .cp-price-box { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .cp-commission-note { font-size: 12px; color: #702054; font-weight: bold; margin-bottom: 30px; text-align: left;}

    .cp-btn { padding: 12px 30px; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer; transition: all 0.3s; border: none; }
    .cp-btn-success { background: #2ecc71; color: white; width: 100%; }
    .cp-btn-success:hover { background: #27ae60; }

    /* PREVIEW PANEL */
    .cp-preview-section { flex: 0 0 320px; position: sticky; top: 100px; }
    .cp-preview-card-wrapper { width: 100%; height: 450px; border-radius: 16px; padding: 3px; background: linear-gradient(135deg, #702054, #f39c12); box-shadow: 0 10px 30px rgba(112, 32, 84, 0.3); }
    .cp-preview-card-inner { width: 100%; height: 100%; border-radius: 13px; background-color: #1a1a1e; background-size: cover; background-position: center; position: relative; overflow: hidden; }
    .cp-preview-card-inner::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 60%; background: linear-gradient(to top, rgba(0,0,0,0.95), transparent); }
    .cp-preview-title { position: absolute; bottom: 25px; left: 20px; right: 20px; color: white; font-size: 22px; font-weight: bold; z-index: 2; word-wrap: break-word; text-align: left; }
    .cp-preview-price { position: absolute; bottom: 60px; left: 20px; color: #f39c12; font-size: 18px; font-weight: bold; z-index: 2; text-align: left; }

    /* Light Mode */
    body.light-mode .cp-form-section { background: #ffffff; border-color: #e2e8f0; }
    body.light-mode .cp-form-section h2 { color: #0f172a; }
    body.light-mode .cp-form-group label { color: #64748b; }
    body.light-mode .cp-input, body.light-mode .cp-select { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .cp-image-upload-wrapper { background: #f8fafc; border-color: #cbd5e1; }
    body.light-mode .cp-main-image-box { background: #ffffff; border-color: #cbd5e1; }
    body.light-mode .cp-custom-file-wrapper { background: #ffffff; border-color: #cbd5e1; color: #64748b; }
    body.light-mode .cp-preview-card-wrapper { background: linear-gradient(135deg, #2563eb, #60a5fa); }
    body.light-mode .cp-preview-card-inner { background-color: #ffffff; }
    body.light-mode .cp-preview-price { color: #2563eb; }
</style>

<div class="cp-wrapper">
    <div class="cp-form-section">
        <h2>Ürünü Düzenle</h2>
        
        <form id="editProductForm">
            <input type="hidden" id="productId" value="<?php echo htmlspecialchars($product['id']); ?>">
            
            <div class="cp-form-group">
                <label>Varlık Türü</label>
                <select id="assetType" class="cp-select">
                    <option value="3d_model" <?php echo $product['type'] == '3d_model' ? 'selected' : ''; ?>>3D Model</option>
                    <option value="ui_kit" <?php echo $product['type'] == 'ui_kit' ? 'selected' : ''; ?>>UI/UX Kit</option>
                    <option value="plugin" <?php echo $product['type'] == 'plugin' ? 'selected' : ''; ?>>Yazılım & Plugin</option>
                    <option value="audio" <?php echo $product['type'] == 'audio' ? 'selected' : ''; ?>>Ses & Müzik</option>
                    <option value="graphics" <?php echo $product['type'] == 'graphics' ? 'selected' : ''; ?>>Grafik & Vektör</option>
                </select>
            </div>

            <div class="cp-form-group">
                <label>Ürün Adı</label>
                <input type="text" id="productName" class="cp-input" value="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div class="cp-form-group">
                <label>Açıklama</label>
                <textarea id="productDetail" class="cp-input" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="cp-form-group">
                <label>Kapak Görselini Değiştir (İsteğe Bağlı)</label>
                <div class="cp-image-upload-wrapper">
                    <div class="cp-main-image-box">
                        <input type="file" id="coverFileInput" accept="image/*">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span id="coverFileText">Yeni Görsel Seç</span>
                        <div class="cp-main-image-preview" id="coverPreview" style="background-image: url('<?php echo htmlspecialchars($product['cover_image'] ?? ''); ?>');"></div>
                    </div>
                </div>
            </div>

            <div class="cp-form-group">
                <label>Ürün Dosyasını Değiştir (İsteğe Bağlı)</label>
                <div class="cp-custom-file-wrapper">
                    <span id="productFileText">Yeni bir dosya yüklemek için tıklayın (Şu anki: <?php echo htmlspecialchars($current_file_name); ?>)</span>
                    <input type="file" id="productFileInput">
                </div>
            </div>

            <div class="cp-price-row">
                <div class="cp-price-box">
                    <label>Satış Fiyatı ($)</label>
                    <input type="number" id="priceTotal" class="cp-input" value="<?php echo htmlspecialchars($product['price_total']); ?>" step="0.01">
                </div>
                <div class="cp-price-box">
                    <label>Net Kazancınız ($)</label>
                    <input type="number" id="priceSeller" class="cp-input" value="<?php echo htmlspecialchars($product['price_seller']); ?>" step="0.01">
                </div>
            </div>
            <div class="cp-commission-note">* Komisyon miktarı %3'tür.</div>

            <button type="button" class="cp-btn cp-btn-success" onclick="updateProduct()">Değişiklikleri Kaydet</button>
        </form>
    </div>

    <div class="cp-preview-section">
        <div class="cp-preview-card-wrapper">
            <div class="cp-preview-card-inner" id="previewCard" style="background-image: url('<?php echo htmlspecialchars($product['cover_image'] ?? ''); ?>');">
                <div class="cp-preview-price" id="previewPrice"><?php echo htmlspecialchars($product['price_total']); ?> $</div>
                <div class="cp-preview-title" id="previewTitle"><?php echo htmlspecialchars($product['name']); ?></div>
            </div>
        </div>
    </div>
</div>

<script>
    const COMMISSION_RATE = <?php echo $commission_rate; ?>;

    // Canlı Başlık Önizleme
    document.getElementById('productName').addEventListener('input', function() {
        document.getElementById('previewTitle').innerText = this.value.trim() === '' ? 'Varlık Adı' : this.value;
    });

    // Fiyat ve Komisyon Hesaplama
    document.getElementById('priceTotal').addEventListener('input', function() {
        let total = parseFloat(this.value) || 0;
        document.getElementById('priceSeller').value = (total * (1 - COMMISSION_RATE)).toFixed(2);
        document.getElementById('previewPrice').innerText = total.toFixed(2) + " $";
    });

    document.getElementById('priceSeller').addEventListener('input', function() {
        let net = parseFloat(this.value) || 0;
        document.getElementById('priceTotal').value = (net / (1 - COMMISSION_RATE)).toFixed(2);
        document.getElementById('previewPrice').innerText = (net / (1 - COMMISSION_RATE)).toFixed(2) + " $";
    });

    // Canlı Resim Önizleme
    document.getElementById('coverFileInput').addEventListener('change', function() {
        if(this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('coverPreview').style.backgroundImage = `url('${e.target.result}')`;
                document.getElementById('previewCard').style.backgroundImage = `url('${e.target.result}')`;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Dosya Adı Önizleme (Ürün Dosyası için)
    const productFileInput = document.getElementById('productFileInput');
    const productFileText = document.getElementById('productFileText');
    productFileInput.addEventListener('change', function() {
        if(this.files && this.files[0]) {
            productFileText.innerText = "Seçilen Yeni Dosya: " + this.files[0].name;
            productFileText.style.color = 'white';
        }
    });

    // AJAX ile Güncelleme İsteği Gönderme
    function updateProduct() {
        const formData = new FormData();
        formData.append('action', 'edit_product');
        formData.append('product_id', document.getElementById('productId').value);
        formData.append('asset_type', document.getElementById('assetType').value);
        formData.append('product_name', document.getElementById('productName').value);
        formData.append('product_detail', document.getElementById('productDetail').value);
        formData.append('price_total', document.getElementById('priceTotal').value);
        formData.append('price_seller', document.getElementById('priceSeller').value);

        // Kapak görselini eklersen
        const coverInput = document.getElementById('coverFileInput');
        if(coverInput.files[0]) {
            formData.append('cover_image_file', coverInput.files[0]);
        }
        
        // Ürün dosyasını eklersen
        if(productFileInput.files[0]) {
            formData.append('product_file', productFileInput.files[0]);
        }

        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data.trim() === 'success') {
                alert('Ürün başarıyla güncellendi!');
                window.location.href = 'productdetail?id=' + document.getElementById('productId').value;
            } else {
                alert('Güncelleme sırasında bir hata oluştu.');
            }
        })
        .catch(err => console.error('Hata:', err));
    }
</script>