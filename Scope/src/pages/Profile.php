<?php
$users = json_decode(file_get_contents(__DIR__ . '/../../backend/database/users_data.json'), true) ?: [];

$user_data = null;
$is_own_profile = false;

$request_user = isset($_GET['user']) ? trim($_GET['user']) : null;

if ($request_user === 'myaccount' || empty($request_user)) {
    if (isset($_SESSION['user_id'])) {
        foreach($users as $u) {
            if($u['id'] === $_SESSION['user_id']) {
                $user_data = $u;
                $is_own_profile = true;
                break;
            }
        }
    }
} else {
    foreach($users as $u) {
        if($u['username'] === $request_user) {
            $user_data = $u;
            break;
        }
    }
    if ($user_data && isset($_SESSION['user_id']) && $user_data['id'] === $_SESSION['user_id']) {
        $is_own_profile = true;
    }
}

if (!$user_data) {
    echo "<div style='text-align:center; padding: 100px; color:white;'><h2>Kullanıcı bulunamadı.</h2></div>";
    exit;
}

// --- VİTRİN VE VARLIKLAR (ASSETS) İÇİN ÜRÜNLERİ ÇEKME ---
$product_file_path = __DIR__ . '/../../backend/database/product_data.json';
$user_products = [];
$user_assets_products = [];
$user_assets_ids = $user_data['assets'] ?? [];

if (file_exists($product_file_path)) {
    $all_products = json_decode(file_get_contents($product_file_path), true) ?: [];
    foreach ($all_products as $product) {
        // Kullanıcının vitrinindeki (kendi eklediği) ürünler
        if (isset($product['seller_id']) && $product['seller_id'] === $user_data['id']) {
            $user_products[] = $product;
        }
        // Kullanıcının satın aldığı varlıklar
        if (in_array($product['id'], $user_assets_ids)) {
            $user_assets_products[] = $product;
        }
    }
}

$avatar_url = (!empty($user_data['avatar']) && file_exists($user_data['avatar'])) ? $user_data['avatar'] : '';
$banner_url = (!empty($user_data['banner']) && file_exists($user_data['banner'])) ? $user_data['banner'] : '';

// Topluluk İstatistikleri
$created_topics_count = count($user_data['created_topics'] ?? []);
$replied_topics_count = count($user_data['replied_topics'] ?? []);
$completed_freelance = $user_data['completed_jobs'] ?? 0;

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';
?>

<style>
    .profile-page-wrapper {
        display: flex;
        flex-direction: row;
        width: 100%;
        max-width: 1600px;
        margin: 10px auto 40px auto;
        gap: 40px;
        padding: 0 20px;
        justify-content: flex-start;
    }
    
    .profile-nav-sidebar {
        width: 200px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .profile-nav-item {
        padding: 12px 20px;
        color: #8b949e;
        text-decoration: none;
        font-size: 14px;
        border-radius: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        text-align: left;
        background: transparent;
        border: none;
        width: 100%;
    }

    .profile-nav-item:hover { background: rgba(255, 255, 255, 0.05); color: white; }
    .profile-nav-item.active {
        background: rgba(243, 156, 18, 0.1);
        color: #f39c12;
        font-weight: 600;
        border-left: 3px solid #f39c12;
    }

    .profile-content-area {
        flex: 1;
        background: rgba(20, 20, 25, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 40px;
        min-height: 600px;
        transition: all 0.4s ease;
    }

    .profile-banner {
        width: calc(100% + 80px);
        margin: -40px -40px 60px -40px;
        height: 200px;
        background-image: url('<?php echo $banner_url; ?>');
        background-size: cover;
        background-position: center;
        background-color: #2a2a35;
        border-radius: 16px 16px 0 0;
        position: relative;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s ease;
    }

    .profile-avatar-large {
        position: absolute;
        bottom: -50px;
        left: 80px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background-image: url('<?php echo $avatar_url; ?>');
        background-size: cover;
        background-position: center;
        background-color: #333;
        border: 4px solid #141419;
        box-shadow: 0 0 20px rgba(0,0,0,0.4);
        transition: all 0.4s ease;
    }

    .profile-info-header {
        margin-left: 220px;
        margin-top: -40px;
        margin-bottom: 40px;
        text-align: left;
    }

    .profile-info-header h2 { font-size: 26px; margin: 0; color: white; transition: color 0.4s ease; }
    .profile-info-header p { color: #8b949e; font-size: 14px; margin: 5px 0; display: flex; align-items: center; gap: 5px; transition: color 0.4s ease; }

    /* YENİ: Topluluk İstatistik Kutuları */
    .profile-stats-row { display: flex; gap: 20px; margin-left: 220px; margin-top: 15px; }
    .stat-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 10px 20px; border-radius: 8px; text-align: center; }
    .stat-box strong { display: block; color: #f39c12; font-size: 1.2rem; }
    .stat-box span { color: #8b949e; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }

    .settings-form { display: flex; flex-direction: column; gap: 20px; max-width: 500px; text-align: left; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group label { font-size: 13px; color: #8b949e; transition: color 0.4s ease;}
    .form-group input {
        background: #1a1a1e; border: 1px solid #333; border-radius: 8px;
        padding: 12px; color: white; outline: none; transition: all 0.3s ease;
    }
    
    .save-btn {
        background: #f39c12; color: black; border: none; padding: 12px 25px;
        border-radius: 8px; font-weight: 600; cursor: pointer; width: fit-content;
        transition: all 0.3s;
    }

    .empty-state-message { color: #555; font-size: 14px; padding: 20px 0; font-style: italic; text-align: left; transition: color 0.4s ease; }
    
    .showcase-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 15px;
    }
    
    .showcase-grid .add-product-btn {
        width: 240px;
        height: 280px;
        border: 2px dashed rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.02);
        cursor: pointer;
        transition: 0.3s all ease;
        color: #8b949e;
        text-decoration: none;
    }
    .showcase-grid .add-product-btn:hover {
        border-color: #f39c12;
        color: #f39c12;
        background: rgba(243, 156, 18, 0.05);
    }
    .showcase-grid .add-product-btn svg {
        width: 48px;
        height: 48px;
        margin-bottom: 12px;
    }

    /* ÜRÜN KARTLARI (Vitrin ve Varlıklar İçin) */
    .product-card { width: 240px; background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; transition: all 0.3s; text-decoration: none; position: relative; }
    .product-card:hover { transform: translateY(-5px); border-color: #f39c12; }
    .product-img { width: 100%; height: 180px; background-color: #0d0d0f; background-size: cover; background-position: center; border-radius: 8px; margin-bottom: 15px; }
    .product-name { color: white; font-size: 1.1rem; margin: 0 0 10px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; transition: color 0.4s ease; }
    .product-price { color: #2ecc71; font-weight: bold; font-size: 1.1rem; margin: 0; }
    
    /* İNDİRME BUTONU EKLENTİSİ */
    .download-btn {
        display: block; width: 100%; text-align: center; background: #2ecc71; color: white;
        padding: 10px 0; border-radius: 6px; font-weight: bold; text-decoration: none;
        margin-top: 10px; transition: all 0.3s; font-size: 0.95rem;
    }
    .download-btn:hover { background: #27ae60; color: white; }
    .download-btn.disabled { background: #555; cursor: not-allowed; }

    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    /* =========================================
       LIGHT MODE (AYDINLIK TEMA) CSS OVERRIDES
       ========================================= */
    body.light-mode .profile-nav-item { color: #64748b; }
    body.light-mode .profile-nav-item:hover { background: rgba(0, 0, 0, 0.05); color: #0f172a; }
    body.light-mode .profile-nav-item.active { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-left-color: #3b82f6; }
    
    body.light-mode .profile-content-area { background: #ffffff; border-color: #e2e8f0; box-shadow: 0 5px 20px rgba(0,0,0,0.04); }
    body.light-mode .profile-banner { background-color: #e2e8f0; border-bottom-color: #cbd5e1; }
    body.light-mode .profile-avatar-large { border-color: #ffffff; box-shadow: 0 0 20px rgba(0,0,0,0.1); background-color: #cbd5e1; }
    
    body.light-mode .profile-info-header h2 { color: #0f172a; }
    body.light-mode .profile-info-header p { color: #64748b; }
    body.light-mode .profile-info-header p svg { color: #3b82f6 !important; }
    
    /* Stats Light Mode */
    body.light-mode .stat-box { background: #f8fafc; border-color: #e2e8f0; }
    body.light-mode .stat-box strong { color: #3b82f6; }
    body.light-mode .stat-box span { color: #64748b; }

    body.light-mode h3 { color: #64748b !important; }
    
    body.light-mode .showcase-grid .add-product-btn { border-color: #cbd5e1; background: #f8fafc; color: #64748b; }
    body.light-mode .showcase-grid .add-product-btn:hover { border-color: #3b82f6; color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
    
    body.light-mode .empty-state-message { color: #64748b; }
    
    body.light-mode .tab-content h2 { color: #0f172a !important; border-bottom-color: #e2e8f0 !important; }
    
    body.light-mode .form-group label { color: #64748b; }
    body.light-mode .form-group input { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .form-group input:focus { border-color: #3b82f6; }
    
    body.light-mode .save-btn { background: #3b82f6; color: #ffffff; }
    body.light-mode .save-btn:hover { background: #2563eb; }

    /* Ürün Kartları Light Mode Override */
    body.light-mode .product-card { background: #ffffff; border-color: #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    body.light-mode .product-card:hover { border-color: #3b82f6; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.08); }
    body.light-mode .product-img { background-color: #f1f5f9; }
    body.light-mode .product-name { color: #0f172a; }
    body.light-mode .download-btn { background: #3b82f6; }
    body.light-mode .download-btn:hover { background: #2563eb; }
</style>

<div class="profile-page-wrapper">
    <div class="profile-nav-sidebar">
        <button class="profile-nav-item <?php echo ($active_tab == 'profile') ? 'active' : ''; ?>" onclick="switchProfileTab('profile', this)">Profile</button>
        
        <?php if($is_own_profile): ?>
            <button class="profile-nav-item <?php echo ($active_tab == 'public') ? 'active' : ''; ?>" onclick="switchProfileTab('public', this)">Public Profile</button>
            <button class="profile-nav-item <?php echo ($active_tab == 'account') ? 'active' : ''; ?>" onclick="switchProfileTab('account', this)">Account</button>
            <button class="profile-nav-item <?php echo ($active_tab == 'assets') ? 'active' : ''; ?>" onclick="switchProfileTab('assets', this)">My Assets</button>
        <?php endif; ?>
    </div>

    <div class="profile-content-area">
        
        <div id="profile" class="tab-content <?php echo ($active_tab == 'profile') ? 'active' : ''; ?>">
            <div class="profile-banner">
                <div class="profile-avatar-large"></div>
            </div>
            <div class="profile-info-header">
                <h2><?php echo htmlspecialchars($user_data['username']); ?></h2>
                <p>
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="color:#f39c12;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                    <?php echo ucfirst($user_data['role'] ?? 'User'); ?>
                </p>
            </div>

            <div class="profile-stats-row">
                <div class="stat-box">
                    <strong><?php echo $created_topics_count; ?></strong>
                    <span>Açılan Konu</span>
                </div>
                <div class="stat-box">
                    <strong><?php echo $replied_topics_count; ?></strong>
                    <span>Katılınan Konu</span>
                </div>
                <div class="stat-box" style="border-color: rgba(46, 204, 113, 0.3);">
                    <strong style="color: #2ecc71;"><?php echo $completed_freelance; ?></strong>
                    <span style="color: #2ecc71;">Başarılı İş</span>
                </div>
            </div>

            <div style="margin-top: 40px; text-align: left;">
                <h3 style="font-size: 14px; color: #8b949e; text-transform: uppercase;">Vitrin</h3>
                
                <div class="showcase-grid">
                    <?php if($is_own_profile): ?>
                        <a href="createproduct" class="add-product-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>Ürün Ekle</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if(!empty($user_products)): ?>
                        <?php foreach($user_products as $product): ?>
                            <?php 
                            $img = (!empty($product['cover_image'])) ? $product['cover_image'] : 'assets/images/default.jpg';
                            ?>
                            <a href="productdetail?id=<?php echo urlencode($product['id']); ?>" class="product-card">
                                <div class="product-img" style="background-image: url('<?php echo htmlspecialchars($img); ?>');"></div>
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-price"><?php echo htmlspecialchars($product['price_total']); ?> $</p>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php if(!$is_own_profile): ?>
                            <div class="empty-state-message">Bu kullanıcının vitrininde şimdilik bir şey yok.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if($is_own_profile): ?>
            <div id="public" class="tab-content <?php echo ($active_tab == 'public') ? 'active' : ''; ?>">
                <h2 style="color:white; text-align:left; border-bottom: 1px solid #333; padding-bottom:15px; margin-bottom:25px;">Public Profile Settings</h2>
                
                <form class="settings-form" action="profile/myaccount" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Profile Picture</label>
                        <input type="file" name="avatar" accept="image/png, image/jpeg">
                    </div>
                    <div class="form-group">
                        <label>Profile Banner</label>
                        <input type="file" name="banner" accept="image/png, image/jpeg">
                    </div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </form>
            </div>

            <div id="account" class="tab-content <?php echo ($active_tab == 'account') ? 'active' : ''; ?>">
                <h2 style="color:white; text-align:left; border-bottom: 1px solid #333; padding-bottom:15px; margin-bottom:25px;">Account Settings</h2>
                <form class="settings-form">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly style="opacity: 0.5;">
                    </div>
                </form>
            </div>

            <div id="assets" class="tab-content <?php echo ($active_tab == 'assets') ? 'active' : ''; ?>">
                <h2 style="color:white; text-align:left; border-bottom: 1px solid #333; padding-bottom:15px; margin-bottom:25px;">My Assets</h2>
                
                <?php if(empty($user_assets_products)): ?>
                    <div class="empty-state-message">Henüz bir varlığınız bulunmuyor. Keşfetmek için mağazaya göz atın.</div>
                <?php else: ?>
                    <div class="showcase-grid">
                        <?php foreach($user_assets_products as $asset): ?>
                            <?php 
                            $img = (!empty($asset['cover_image'])) ? $asset['cover_image'] : 'assets/images/default.jpg';
                            $file_link = (!empty($asset['product_file'])) ? $asset['product_file'] : '#';
                            ?>
                            <div class="product-card">
                                <div class="product-img" style="background-image: url('<?php echo htmlspecialchars($img); ?>');"></div>
                                <h3 class="product-name"><?php echo htmlspecialchars($asset['name']); ?></h3>
                                
                                <?php if($file_link !== '#'): ?>
                                    <a href="<?php echo htmlspecialchars($file_link); ?>" download class="download-btn">Dosyayı İndir</a>
                                <?php else: ?>
                                    <button class="download-btn disabled" disabled>Dosya Bulunamadı</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    function switchProfileTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.profile-nav-item').forEach(item => item.classList.remove('active'));
        
        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');
        
        const newUrl = window.location.pathname + '?tab=' + tabId;
        window.history.pushState({path:newUrl},'',newUrl);
    }
</script>