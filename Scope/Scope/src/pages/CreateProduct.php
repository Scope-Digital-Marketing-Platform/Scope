<?php
// Oturum başlatılmamışsa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Güvenlik Kontrolü: Kullanıcı giriş yapmamışsa
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "<script>
        alert('Ürün eklemek için lütfen önce giriş yapın.');
        window.location.href = 'home';
    </script>";
    exit;
}

$commission_rate = 0.03; 
?>

<style>
    .cp-wrapper {
        display: flex;
        flex-direction: row; /* Önizleme sağda, form solda olması için row */
        width: 100%;
        max-width: 1400px;
        margin: 40px auto;
        gap: 50px;
        padding: 0 20px;
        align-items: flex-start;
    }

    /* FORM ALANI (SOL) */
    .cp-form-section {
        flex: 1;
        background: rgba(20, 20, 25, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 40px;
        min-height: 500px;
        position: relative;
        transition: background-color 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
    }

    .cp-form-section h2 {
        color: rgba(255, 255, 255, 0.2);
        text-align: center;
        font-size: 32px;
        margin-top: 0;
        margin-bottom: 40px;
        text-transform: uppercase;
        letter-spacing: 5px;
        transition: color 0.4s ease;
    }

    /* ADIM KAPSAYICILARI */
    .cp-step {
        display: none;
        animation: fadeIn 0.4s ease-in-out;
    }
    .cp-step.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* FORM ELEMANLARI */
    .cp-form-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 25px;
    }

    .cp-form-group label {
        font-size: 14px;
        color: #8b949e;
        font-weight: 500;
        transition: color 0.4s ease;
    }

    .cp-input, .cp-select {
        background: #1a1a1e;
        border: 1px solid #333;
        border-radius: 8px;
        padding: 14px;
        color: white;
        outline: none;
        font-size: 14px;
        transition: all 0.3s;
        width: 100%;
        box-sizing: border-box;
    }
    .cp-input:focus, .cp-select:focus { border-color: #f39c12; }
    
    .cp-select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238b949e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
    }

    /* GÖRSEL YÜKLEME KOMBİNE ALANI */
    .cp-image-upload-wrapper {
        display: flex;
        border: 2px dashed #333;
        border-radius: 8px;
        padding: 20px;
        gap: 20px;
        background: #151518;
        align-items: stretch;
        transition: all 0.3s ease;
    }

    .cp-main-image-box {
        flex: 0 0 200px;
        height: 200px;
        background: #1a1a1e;
        border: 1px solid #444;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: border-color 0.3s, background-color 0.3s;
        text-align: center;
        padding: 10px;
    }
    .cp-main-image-box:hover { border-color: #f39c12; }
    .cp-main-image-box input[type="file"] {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        opacity: 0; cursor: pointer; z-index: 10;
    }
    .cp-main-image-box svg { width: 40px; height: 40px; color: #8b949e; margin-bottom: 10px; transition: color 0.3s; }
    .cp-main-image-box span { color: #8b949e; font-size: 13px; font-weight: bold; transition: color 0.3s; }
    .cp-main-image-preview {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background-size: cover; background-position: center; z-index: 5; pointer-events: none;
    }

    /* --- YENİ EKLENEN SİLME BUTONU CSS --- */
    .cp-remove-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(231, 76, 60, 0.85);
        color: white;
        border: none;
        border-radius: 50%;
        width: 26px;
        height: 26px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s;
    }
    .cp-remove-btn:hover {
        background: rgba(231, 76, 60, 1);
    }
    /* --- SON --- */

    .cp-gallery-area {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
        overflow-x: auto;
        padding-left: 20px;
        border-left: 1px solid #333;
        transition: border-color 0.3s;
    }
    .cp-gallery-box {
        flex: 0 0 100px;
        height: 100px;
        border: 1px solid #444;
        border-radius: 8px;
        background-color: #1a1a1e;
        display: flex;
        align-items: center;
        justify-content: center;
        background-size: cover;
        background-position: center;
        position: relative;
        transition: border-color 0.3s, background-color 0.3s;
    }
    .cp-gallery-add {
        flex: 0 0 100px;
        height: 100px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 1px dashed #555;
        border-radius: 8px;
        color: #8b949e;
        cursor: pointer;
        position: relative;
        transition: border-color 0.3s, color 0.3s;
    }
    .cp-gallery-add:hover { border-color: #f39c12; color: #f39c12; }
    .cp-gallery-add input[type="file"] {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        opacity: 0; cursor: pointer;
    }
    .cp-gallery-add svg { width: 24px; height: 24px; margin-bottom: 5px; }
    .cp-gallery-add span { font-size: 12px; }

    .cp-price-row {
        display: flex;
        gap: 20px;
        margin-bottom: 10px;
    }
    .cp-price-box {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .cp-commission-note {
        font-size: 12px;
        color: #702054;
        font-weight: bold;
        margin-bottom: 30px;
        transition: color 0.3s;
    }

    .cp-custom-file-wrapper {
        position: relative;
        background: #1a1a1e;
        border: 1px dashed #444;
        border-radius: 8px;
        padding: 20px;
        color: #8b949e;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        text-align: center;
    }
    .cp-custom-file-wrapper:hover { border-color: #f39c12; }
    .cp-custom-file-wrapper input[type="file"] {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        opacity: 0 !important; cursor: pointer; z-index: 10;
    }

    .cp-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        padding-top: 25px;
        transition: border-color 0.3s;
    }
    .cp-btn {
        padding: 12px 30px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }
    .cp-btn-secondary {
        background: transparent;
        color: #8b949e;
        border: 1px solid #444;
    }
    .cp-btn-secondary:hover { background: #222; color: white; }
    
    .cp-btn-primary {
        background: #f39c12;
        color: black;
        border: none;
    }
    .cp-btn-primary:hover { opacity: 0.9; }

    .cp-btn-success {
        background: transparent;
        color: #2ecc71;
        border: 2px solid #2ecc71;
    }
    .cp-btn-success:hover { background: #2ecc71; color: black; }

    /* ÖNİZLEME ALANI (SAĞ) */
    .cp-preview-section {
        flex: 0 0 320px;
        position: sticky;
        top: 100px;
    }
    
    .cp-preview-card-wrapper {
        width: 100%;
        height: 450px;
        border-radius: 16px;
        padding: 3px; 
        background: linear-gradient(135deg, #702054, #f39c12); 
        box-shadow: 0 10px 30px rgba(112, 32, 84, 0.3);
        transition: all 0.4s ease;
    }

    .cp-preview-card-inner {
        width: 100%;
        height: 100%;
        border-radius: 13px; 
        background-color: #1a1a1e;
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
        transition: background-image 0.3s ease, background-color 0.4s ease;
    }
    
    .cp-preview-card-inner::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 60%;
        background: linear-gradient(to top, rgba(0,0,0,0.95), transparent);
    }

    .cp-preview-title {
        position: absolute;
        bottom: 25px;
        left: 20px;
        right: 20px;
        color: white;
        font-size: 22px;
        font-weight: bold;
        z-index: 2;
        word-wrap: break-word;
        transition: color 0.4s ease;
    }
    
    .cp-preview-price {
        position: absolute;
        bottom: 60px;
        left: 20px;
        color: #f39c12;
        font-size: 18px;
        font-weight: bold;
        z-index: 2;
        transition: color 0.4s ease;
    }

    /* Modal Stilleri */
    .cp-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.8); backdrop-filter: blur(5px);
        z-index: 2000; display: none; align-items: center; justify-content: center;
    }
    .cp-modal-content {
        background: #1a1a1e; border: 1px solid #333; border-radius: 12px;
        width: 500px; padding: 30px; text-align: left;
        transition: all 0.3s ease;
    }
    .cp-modal-content h3 { margin-top: 0; color: white; border-bottom: 1px solid #333; padding-bottom: 15px; transition: all 0.3s; }
    .cp-terms-box {
        background: #0d0d0f; border: 1px solid #333; padding: 15px;
        border-radius: 8px; height: 150px; overflow-y: auto; color: #8b949e;
        font-size: 13px; margin-bottom: 20px; transition: all 0.3s;
    }
    .cp-checkbox-group {
        display: flex; align-items: center; gap: 10px; color: white;
        cursor: pointer; margin-bottom: 20px; transition: color 0.3s;
    }
    .cp-modal-actions { display: flex; justify-content: flex-end; gap: 15px; }
    .cp-btn-cancel { background: transparent; color: white; border: 1px solid #555; padding: 10px 20px; border-radius: 6px; cursor: pointer; transition: 0.3s;}
    .cp-btn-confirm { background: #2ecc71; color: black; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; opacity: 0.5; pointer-events: none; transition: 0.3s; }
    .cp-btn-confirm.enabled { opacity: 1; pointer-events: auto; }

    /* =========================================
       LIGHT MODE (AYDINLIK TEMA) CSS OVERRIDES
       ========================================= */
    body.light-mode .cp-form-section { background: #ffffff; border-color: #e2e8f0; box-shadow: 0 5px 20px rgba(0,0,0,0.04); }
    body.light-mode .cp-form-section h2 { color: #0f172a; }
    body.light-mode .cp-form-group label { color: #64748b; }
    
    body.light-mode .cp-input, body.light-mode .cp-select { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .cp-input:focus, body.light-mode .cp-select:focus { border-color: #3b82f6; }
    
    body.light-mode .cp-image-upload-wrapper { background: #f8fafc; border-color: #cbd5e1; }
    body.light-mode .cp-main-image-box { background: #ffffff; border-color: #cbd5e1; }
    body.light-mode .cp-main-image-box:hover { border-color: #3b82f6; }
    body.light-mode .cp-main-image-box svg, body.light-mode .cp-main-image-box span { color: #64748b; }
    
    body.light-mode .cp-gallery-area { border-color: #e2e8f0; }
    body.light-mode .cp-gallery-box { background-color: #ffffff; border-color: #cbd5e1; }
    body.light-mode .cp-gallery-add { border-color: #cbd5e1; color: #64748b; }
    body.light-mode .cp-gallery-add:hover { border-color: #3b82f6; color: #3b82f6; }
    
    body.light-mode .cp-commission-note { color: #3b82f6; }
    
    body.light-mode .cp-custom-file-wrapper { background: #ffffff; border-color: #cbd5e1; color: #64748b; }
    body.light-mode .cp-custom-file-wrapper:hover { border-color: #3b82f6; }
    
    body.light-mode .cp-actions { border-color: #e2e8f0; }
    body.light-mode .cp-btn-secondary { color: #475569; border-color: #cbd5e1; }
    body.light-mode .cp-btn-secondary:hover { background: #f1f5f9; color: #0f172a; }
    body.light-mode .cp-btn-primary { background: #3b82f6; color: #ffffff; }
    body.light-mode .cp-btn-primary:hover { background: #2563eb; }
    
    body.light-mode .cp-preview-card-wrapper { background: linear-gradient(135deg, #2563eb, #60a5fa); box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3); }
    body.light-mode .cp-preview-card-inner { background-color: #ffffff; }
    body.light-mode .cp-preview-price { color: #60a5fa; }
    
    body.light-mode .cp-modal-content { background: #ffffff; border-color: #e2e8f0; }
    body.light-mode .cp-modal-content h3 { color: #0f172a; border-color: #e2e8f0; }
    body.light-mode .cp-terms-box { background: #f8fafc; border-color: #cbd5e1; color: #475569; }
    body.light-mode .cp-checkbox-group { color: #1e293b; }
    body.light-mode .cp-btn-cancel { color: #475569; border-color: #cbd5e1; }
    body.light-mode .cp-btn-cancel:hover { background: #f1f5f9; }
</style>

<div class="cp-wrapper">

    <div class="cp-form-section">
        <h2>Create Product</h2>

        <div id="step1" class="cp-step active">
            
            <div class="cp-form-group">
                <label>Varlık Türü</label>
                <select id="assetType" class="cp-select">
                    <option value="" disabled selected>Kategori Seçin...</option>
                    <option value="3d_model">3D Model</option>
                    <option value="ui_kit">UI/UX Kit</option>
                    <option value="plugin">Yazılım & Plugin</option>
                    <option value="audio">Ses & Müzik</option>
                    <option value="graphics">Grafik & Vektör</option>
                </select>
            </div>

            <div class="cp-form-group">
                <label>Ürün Adı</label>
                <input type="text" id="productName" class="cp-input" placeholder="Örn: Modern UI Dashboard Kiti">
            </div>

            <div class="cp-form-group">
                <label>Açıklama</label>
                <textarea id="productDetail" class="cp-input" rows="4" placeholder="Ürününüzü detaylı bir şekilde tanıtın..."></textarea>
            </div>

            <div class="cp-form-group">
                <label>Görseller (Ana Görsel ve İnceleme Görselleri)</label>
                <div class="cp-image-upload-wrapper">
                    <div class="cp-main-image-box">
                        <input type="file" id="coverFileInput" accept="image/*">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span id="coverFileText">Ürün Ana Görseli Seç</span>
                        <div class="cp-main-image-preview" id="coverPreview"></div>
                        <button type="button" class="cp-remove-btn" id="coverRemoveBtn" style="display:none;">&times;</button>
                    </div>

                    <div class="cp-gallery-area" id="galleryContainer">
                        <label class="cp-gallery-add" id="galleryAddBtn">
                            <input type="file" id="galleryInput" accept="image/*" multiple>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span id="galleryCounter">0 / 3</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="cp-actions">
                <div></div> <button type="button" class="cp-btn cp-btn-primary" id="btnNextToStep2">İleri &rarr;</button>
            </div>
        </div>

        <div id="step2" class="cp-step">
            
            <div class="cp-form-group">
                <label>Ürün Dosyası (Alıcı İndirecek)</label>
                <div class="cp-custom-file-wrapper">
                    <span id="productFileText">Satılacak dosyayı seçmek için tıklayın (Tüm formatlar desteklenir)</span>
                    <input type="file" id="productFileInput">
                </div>
            </div>

            <div class="cp-price-row">
                <div class="cp-price-box">
                    <label>Alıcının Ödeyeceği (Satış Fiyatı $)</label>
                    <input type="number" id="priceTotal" class="cp-input" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="cp-price-box">
                    <label>Sizin Alacağınız (Net Kazanç $)</label>
                    <input type="number" id="priceSeller" class="cp-input" placeholder="0.00" min="0" step="0.01">
                </div>
            </div>
            <div class="cp-commission-note">* Komisyon miktarı %3'tür.</div>

            <div class="cp-actions">
                <button type="button" class="cp-btn cp-btn-secondary" id="btnBackToStep1">&larr; Geri</button>
                <button type="button" class="cp-btn cp-btn-success" id="openModalBtn">Yayınla</button>
            </div>
        </div>

    </div>

    <div class="cp-preview-section">
        <div class="cp-preview-card-wrapper">
            <div class="cp-preview-card-inner" id="previewCard">
                <div class="cp-preview-price" id="previewPrice">0.00 $</div>
                <div class="cp-preview-title" id="previewTitle">Varlık Adı</div>
            </div>
        </div>
    </div>

</div>

<div class="cp-modal-overlay" id="termsModal">
    <div class="cp-modal-content">
        <h3>Satış Sözleşmesi & Kurallar</h3>
        <div class="cp-terms-box">
            <p>1. Scope platformuna yüklediğiniz ürünlerin yasal sahibi olduğunuzu kabul edersiniz.</p>
            <p>2. İçerikte zararlı yazılım (virüs, trojan vb.) barındırılamaz.</p>
            <p>3. Satışlardan kesilecek olan komisyon oranı %3 olarak belirlenmiş olup, bu kesinti net kazancınıza yansıtılmıştır.</p>
            <p>4. Kurallara aykırı ürünler onaylanmadan sistemden kaldırılır ve hesabınız askıya alınabilir.</p>
        </div>
        <label class="cp-checkbox-group">
            <input type="checkbox" id="termsCheckbox">
            Sözleşmeyi okudum ve kuralları kabul ediyorum.
        </label>
        <div class="cp-modal-actions">
            <button class="cp-btn-cancel" id="cancelModalBtn">İptal</button>
            <button class="cp-btn-confirm" id="finalPublishBtn">Onayla ve Yayınla</button>
        </div>
    </div>
</div>

<script>
    const COMMISSION_RATE = <?php echo $commission_rate; ?>; 

    // --- STEP (AŞAMA) GEÇİŞ KONTROLLERİ ---
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const btnNextToStep2 = document.getElementById('btnNextToStep2');
    const btnBackToStep1 = document.getElementById('btnBackToStep1');

    btnNextToStep2.addEventListener('click', () => {
        // Step 1'den Step 2'ye geçerken ufak bir validasyon yapılabilir
        if(document.getElementById('productName').value.trim() === '' || document.getElementById('assetType').value === '') {
            alert('Lütfen Varlık Türü ve Ürün Adı alanlarını doldurun.');
            return;
        }
        step1.classList.remove('active');
        step2.classList.add('active');
    });

    btnBackToStep1.addEventListener('click', () => {
        step2.classList.remove('active');
        step1.classList.add('active');
    });

    // --- ÖNİZLEME KARTI CANLI GÜNCELLEME ---
    const productNameInput = document.getElementById('productName');
    const previewTitle = document.getElementById('previewTitle');
    
    productNameInput.addEventListener('input', function() {
        previewTitle.innerText = this.value.trim() === '' ? 'Varlık Adı' : this.value;
    });

    // --- FİYAT VE KOMİSYON HESAPLAMA ---
    const priceTotalInput = document.getElementById('priceTotal');
    const priceSellerInput = document.getElementById('priceSeller');
    const previewPrice = document.getElementById('previewPrice');

    priceTotalInput.addEventListener('input', function() {
        let total = parseFloat(this.value) || 0;
        let net = total * (1 - COMMISSION_RATE);
        priceSellerInput.value = net.toFixed(2);
        previewPrice.innerText = total.toFixed(2) + " $";
    });

    priceSellerInput.addEventListener('input', function() {
        let net = parseFloat(this.value) || 0;
        let total = net / (1 - COMMISSION_RATE);
        priceTotalInput.value = total.toFixed(2);
        previewPrice.innerText = total.toFixed(2) + " $";
    });

    // --- ANA GÖRSEL YÜKLEME VE SİLME ---
    const coverFileInput = document.getElementById('coverFileInput');
    const coverPreview = document.getElementById('coverPreview');
    const previewCardInner = document.getElementById('previewCard');
    const coverFileText = document.getElementById('coverFileText');
    const coverRemoveBtn = document.getElementById('coverRemoveBtn'); // EKLENDİ

    coverFileInput.addEventListener('change', function() {
        if(this.files && this.files[0]) {
            coverFileText.style.display = 'none'; // Yazıyı gizle
            let reader = new FileReader();
            reader.onload = function(e) { 
                // Hem sol taraftaki kutuda hem de sağdaki önizleme kartında göster
                coverPreview.style.backgroundImage = `url('${e.target.result}')`; 
                previewCardInner.style.backgroundImage = `url('${e.target.result}')`; 
                coverRemoveBtn.style.display = 'flex'; // Silme butonunu göster (EKLENDİ)
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // ANA GÖRSEL SİLME İŞLEMİ (EKLENDİ)
    coverRemoveBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Dosya seçiminin tetiklenmesini önler
        coverFileInput.value = ''; 
        coverPreview.style.backgroundImage = 'none';
        previewCardInner.style.backgroundImage = 'none';
        coverFileText.style.display = 'block'; // Yazıyı geri getir
        coverRemoveBtn.style.display = 'none'; // Butonu gizle
    });

    // --- GALERİ GÖRSELLERİ YÜKLEME VE SİLME (MAX 3) ---
    const galleryInput = document.getElementById('galleryInput');
    const galleryContainer = document.getElementById('galleryContainer');
    const galleryCounter = document.getElementById('galleryCounter');
    const galleryAddBtn = document.getElementById('galleryAddBtn');
    let selectedGalleryFiles = [];

    galleryInput.addEventListener('change', function(e) {
        const files = Array.from(this.files);
        files.forEach(file => {
            if (selectedGalleryFiles.length < 3) {
                selectedGalleryFiles.push(file); 
                let reader = new FileReader();
                reader.onload = function(event) {
                    const box = document.createElement('div');
                    box.className = 'cp-gallery-box';
                    box.style.backgroundImage = `url('${event.target.result}')`;
                    
                    // GALERİ SİLME BUTONU EKLENDİ
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'cp-remove-btn';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const index = selectedGalleryFiles.indexOf(file);
                        if (index > -1) {
                            selectedGalleryFiles.splice(index, 1); // Dosyayı array'den çıkar
                        }
                        box.remove(); // DOM'dan kutuyu sil
                        galleryCounter.innerText = `${selectedGalleryFiles.length} / 3`; // Sayacı güncelle
                        galleryAddBtn.style.display = 'flex'; // Sınırın altına inildiği için ekleme butonunu tekrar göster
                    });
                    box.appendChild(removeBtn);

                    // Butondan önce yeni kutuyu ekle
                    galleryContainer.insertBefore(box, galleryAddBtn);
                    
                    galleryCounter.innerText = `${selectedGalleryFiles.length} / 3`;
                    if(selectedGalleryFiles.length >= 3) { 
                        galleryAddBtn.style.display = 'none'; 
                    }
                }
                reader.readAsDataURL(file);
            }
        });
        this.value = ''; 
    });

    // --- DOSYA YÜKLEME (ZIP, RAR VS) ---
    const productFileInput = document.getElementById('productFileInput');
    const productFileText = document.getElementById('productFileText');
    productFileInput.addEventListener('change', function() {
        if(this.files && this.files[0]) {
            productFileText.innerText = this.files[0].name;
            productFileText.style.color = 'white';
        }
    });

    // --- MODAL VE YAYINLAMA ---
    const openModalBtn = document.getElementById('openModalBtn');
    const termsModal = document.getElementById('termsModal');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const termsCheckbox = document.getElementById('termsCheckbox');
    const finalPublishBtn = document.getElementById('finalPublishBtn');

    openModalBtn.addEventListener('click', () => { 
        const isNameFilled = productNameInput.value.trim() !== '';
        const isAssetTypeSelected = document.getElementById('assetType').value !== '';
        const isPriceFilled = priceTotalInput.value.trim() !== '' && parseFloat(priceTotalInput.value) >= 0;
        const isProductFileFilled = productFileInput.files.length > 0;
        const isCoverFilled = coverFileInput.files.length > 0;

        if (!isNameFilled || !isAssetTypeSelected || !isPriceFilled || !isProductFileFilled || !isCoverFilled) {
            alert("Lütfen yayınlamadan önce tüm zorunlu alanları (Kategori, İsim, Dosya, Kapak Görseli, Fiyat) eksiksiz doldurun.");
            return; 
        }

        termsModal.style.display = 'flex'; 
    });

    cancelModalBtn.addEventListener('click', () => { 
        termsModal.style.display = 'none'; 
        termsCheckbox.checked = false; 
        finalPublishBtn.classList.remove('enabled'); 
    });

    termsCheckbox.addEventListener('change', function() { 
        if(this.checked) { finalPublishBtn.classList.add('enabled'); } 
        else { finalPublishBtn.classList.remove('enabled'); } 
    });

    finalPublishBtn.addEventListener('click', () => {
        finalPublishBtn.innerText = "Yükleniyor...";
        finalPublishBtn.style.pointerEvents = "none";

        const formData = new FormData();
        formData.append('action', 'create_product');
        // HTML'deki <select id="assetType"> değerini alıyoruz
        formData.append('asset_type', document.getElementById('assetType').value); 
        formData.append('product_name', productNameInput.value);
        formData.append('product_detail', document.getElementById('productDetail').value);
        formData.append('price_total', priceTotalInput.value);
        formData.append('price_seller', priceSellerInput.value);

        if (coverFileInput.files[0]) {
            formData.append('cover_image_file', coverFileInput.files[0]);
        }

        selectedGalleryFiles.forEach(file => {
            formData.append('gallery_images[]', file);
        });

        if (productFileInput.files[0]) {
            formData.append('product_file', productFileInput.files[0]);
        }

        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            window.location.href = 'profile/myaccount';
        })
        .catch(err => {
            console.error(err);
            finalPublishBtn.innerText = "Hata Oluştu!";
            finalPublishBtn.style.pointerEvents = "auto";
        });
    });
</script>