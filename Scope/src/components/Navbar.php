<?php
$is_logged_in = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
$current_page = isset($_GET['page']) ? strtolower($_GET['page']) : 'home';

// --- SEPET, BİLDİRİM VE MESAJ VERİLERİNİ ÇEKME ---
$cart_items = [];
$cart_total = 0;
$user_cart_ids = [];
$notifications = [];
$unread_notifs = 0;
$unread_messages = 0;

if ($is_logged_in) {
    $users_db_path = __DIR__ . '/../../backend/database/users_data.json';
    $products_db_path = __DIR__ . '/../../backend/database/product_data.json';
    $social_db_path = __DIR__ . '/../../backend/database/social_data.json';
    
    $users_db = file_exists($users_db_path) ? json_decode(file_get_contents($users_db_path), true) ?: [] : [];
    $products_db = file_exists($products_db_path) ? json_decode(file_get_contents($products_db_path), true) ?: [] : [];
    $social_db = file_exists($social_db_path) ? json_decode(file_get_contents($social_db_path), true) ?: [] : [];
    
    foreach($users_db as $u) {
        if (isset($_SESSION['user_id']) && $u['id'] === $_SESSION['user_id']) {
            $user_cart_ids = $u['cart'] ?? [];
            $notifications = $u['notifications'] ?? [];
            foreach($notifications as $n) { if(!$n['read']) $unread_notifs++; }
            break;
        }
    }
    
    foreach($user_cart_ids as $cid) {
        foreach($products_db as $p) {
            if ($p['id'] === $cid) { $cart_items[] = $p; $cart_total += (float)$p['price_total']; break; }
        }
    }

    // DÜZELTİLDİ: Düz mesaj veritabanından okunmamış mesajları say
    foreach($social_db as $msg) {
        // Eğer mesajın alıcısı bizsek ve mesaj okunmamışsa sayacı artır
        if (isset($msg['receiver_id']) && $msg['receiver_id'] === $_SESSION['user_id'] && empty($msg['read'])) {
            $unread_messages++;
        }
    }
}
?>

<script>
    // FOUC ENGELLEYİCİ: Geçiş animasyonlarını sayfa yüklenene kadar durdur, body'ye sınıfı anında ver!
    document.documentElement.classList.add('preload-transitions');
    if (localStorage.getItem('theme') === 'light') {
        document.documentElement.classList.add('light-mode');
        document.body.classList.add('light-mode');
    }
    window.addEventListener('load', () => {
        document.documentElement.classList.remove('preload-transitions');
    });
</script>

<style>
    /* PRELOAD GEÇİŞ ENGELLEME */
    .preload-transitions * { transition: none !important; }

    body { background-color: #0d0d0f; color: #ffffff; background-image: radial-gradient(circle at 0% 0%, rgba(68, 17, 60, 0.3) 0%, transparent 40%), radial-gradient(circle at 100% 100%, rgba(145, 60, 30, 0.2) 0%, transparent 50%); background-attachment: fixed; margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; transition: background-color 0.4s ease, color 0.4s ease; }
    
    .announcement-bar { position: relative; height: 35px; background: #0a0a0c; display: flex; align-items: center; justify-content: center; overflow: hidden; border-bottom: 1px solid rgba(255, 255, 255, 0.05); transition: background-color 0.4s ease; }
    .announcement-text { position: absolute; width: 100%; text-align: center; font-size: 13px; color: #f39c12; font-weight: 500; opacity: 0; transform: translateY(100%); transition: all 0.5s ease-in-out; }
    .announcement-text.show { opacity: 1; transform: translateY(0); }
    .announcement-text.exit { opacity: 0; transform: translateY(-100%); }

    .sticky-header-container { position: sticky; top: 0; z-index: 1000; width: 100%; pointer-events: none; }
    .navbar { display: flex; justify-content: space-between; align-items: center; padding: 0 40px; height: 70px; background: rgba(13, 13, 15, 0.85); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); position: relative; z-index: 2; pointer-events: auto; transition: background-color 0.4s ease; }

    .nav-brand-container { flex: 1; display: flex; justify-content: flex-start; }
    .nav-brand { font-size: 22px; font-weight: 700; text-decoration: none; color: white; letter-spacing: 2px; }

    .nav-search { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); display: flex; width: 100%; max-width: 450px; }
    .nav-search input { width: 100%; padding: 10px 15px; background-color: #1a1a1e; border: 1px solid #333; color: white; border-radius: 8px 0 0 8px; outline: none; transition: all 0.3s; }
    .nav-search input:focus { border-color: #7a3b52; }
    .nav-search button { padding: 10px 20px; border: 1px solid #333; border-left: none; background-color: #222227; color: white; cursor: pointer; border-radius: 0 8px 8px 0; transition: all 0.3s; }
    .nav-search button:hover { background-color: #33333b; }

    .nav-right-container { flex: 1; display: flex; justify-content: flex-end; align-items: center; gap: 20px; }
    .nav-icons { display: flex; align-items: center; gap: 15px; margin-right: 10px; }
    .nav-icons a { color: #b0b0b0; transition: color 0.2s; display: flex; align-items: center; position: relative;}
    .nav-icons a:hover { color: #f39c12; }
    
    /* İkon Baloncukları */
    .icon-badge { position: absolute; top: -6px; right: -8px; background: #e74c3c; color: white; font-size: 10px; font-weight: bold; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; }
    .icon-badge.cart-color { background: #f39c12; color: #111; }

    .login-text-btn { color: #e0e0e0; text-decoration: none; font-weight: 500; font-size: 15px; cursor: pointer; transition: color 0.2s; }
    .login-text-btn:hover { color: #f39c12; }
    .signup-btn { background: linear-gradient(135deg, #a4452f, #702054); color: white; text-decoration: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; box-shadow: 0 2px 8px rgba(112, 32, 84, 0.4); }
    .signup-btn:hover { opacity: 0.9; }

    .profile-container { position: relative; }
    .profile-avatar { width: 38px; height: 38px; background: linear-gradient(135deg, #a4452f, #702054); border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-weight: bold; user-select: none; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
    .profile-dropdown { position: absolute; top: 50px; right: 0; width: 200px; background-color: #161b22; border: 1px solid #30363d; border-radius: 6px; box-shadow: 0 8px 24px rgba(0,0,0,0.2); display: none; flex-direction: column; z-index: 1001; overflow: hidden; }
    .profile-dropdown.open { display: flex; }
    .dropdown-header { padding: 10px 16px; border-bottom: 1px solid #30363d; font-size: 13px; color: #8b949e; }
    .dropdown-header strong { color: #c9d1d9; font-size: 14px; display: block; margin-top: 4px; }
    .profile-dropdown a { padding: 8px 16px; color: #c9d1d9; text-decoration: none; font-size: 14px; transition: background 0.1s; }
    .profile-dropdown a:hover { background-color: #1f242c; color: white; }
    .dropdown-divider { height: 1px; background-color: #30363d; margin: 4px 0; }

    /* ================= SEPET DROPDOWN ================= */
    .cart-container { position: relative; display: flex; align-items: center; }
    .cart-dropdown { display: none; position: absolute; top: 40px; right: 0; width: 320px; background-color: #161b22; border: 1px solid #30363d; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 1002; flex-direction: column; overflow: hidden; }
    .cart-dropdown.open { display: flex; }
    .cart-header { padding: 12px 15px; border-bottom: 1px solid #30363d; font-size: 13px; color: #8b949e; font-weight: bold; }
    .cart-items-wrapper { max-height: 250px; overflow-y: auto; }
    .cart-empty { padding: 20px; text-align: center; color: #8b949e; font-size: 13px; }
    .cart-item { display: flex; gap: 10px; padding: 12px 15px; border-bottom: 1px dashed rgba(255,255,255,0.1); align-items: center; }
    .cart-item-img { width: 45px; height: 35px; background: #222; border-radius: 4px; background-size: cover; background-position: center; }
    .cart-item-info { flex: 1; overflow: hidden; }
    .cart-item-title { color: white; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
    .cart-item-price { color: #f39c12; font-weight: bold; font-size: 12px; }
    .cart-item-remove-btn { background: transparent; border: none; color: #ff4a4a; cursor: pointer; transition: 0.3s; padding: 5px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .cart-item-remove-btn:hover { color: white; background: #ff4a4a; transform: scale(1.1); }
    .cart-more { padding: 8px; text-align: center; color: #8b949e; font-size: 11px; }
    .cart-footer { padding: 15px; background: rgba(0,0,0,0.2); }
    .cart-total-row { display: flex; justify-content: space-between; margin-bottom: 15px; color: white; font-weight: bold; }
    .cart-checkout-btn { display: block; width: 100%; text-align: center; background: #f39c12; color: #111; padding: 10px 0; border-radius: 6px; font-weight: bold; text-decoration: none; font-size: 14px; transition: all 0.3s; text-transform: uppercase; }
    .cart-checkout-btn:hover { background: #f1c40f; opacity: 0.9; }

    /* ================= BİLDİRİM DROPDOWN ================= */
    .notif-container { position: relative; display: flex; align-items: center; }
    .notif-dropdown { display: none; position: absolute; top: 40px; right: -60px; width: 300px; background-color: #161b22; border: 1px solid #30363d; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 1002; flex-direction: column; overflow: hidden; }
    .notif-dropdown.open { display: flex; }
    .notif-header { padding: 12px 15px; border-bottom: 1px solid #30363d; font-size: 13px; color: #8b949e; font-weight: bold; display: flex; justify-content: space-between; }
    .notif-items-wrapper { max-height: 300px; overflow-y: auto; }
    .notif-empty { padding: 20px; text-align: center; color: #8b949e; font-size: 13px; }
    .notif-item { padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 5px; text-decoration: none; transition: background 0.2s; }
    .notif-item:hover { background: rgba(255,255,255,0.05); }
    .notif-item.unread { background: rgba(243, 156, 18, 0.05); border-left: 3px solid #f39c12; }
    .notif-text { color: white; font-size: 13px; line-height: 1.4; }
    .notif-date { color: #8b949e; font-size: 11px; }

    /* Alt Navbar Tasarımı */
    .sub-nav-wrapper { display: flex; justify-content: center; position: relative; width: 100%; pointer-events: none; margin-top: -1px; z-index: 1; }
    .sub-navbar { pointer-events: auto; background: rgba(25, 25, 30, 0.6); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.06); border-top: none; border-radius: 0 0 16px 16px; padding: 10px 40px; display: flex; gap: 35px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); position: relative; align-items: center; transition: background-color 0.4s ease; }
    .sub-navbar a { color: #8b949e; text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.3s ease; padding-bottom: 6px; position: relative; z-index: 2; cursor: pointer; display: flex; align-items: center; }
    .sub-navbar a:hover, .sub-navbar a.active { color: #ffffff; }
    .sub-navbar .add-product-btn { display: flex; align-items: center; justify-content: center; color: #8b949e; transition: all 0.3s ease; padding: 0; margin-left: -15px; }
    .sub-navbar .add-product-btn svg { width: 26px; height: 26px; transition: all 0.3s ease; }
    .sub-navbar .add-product-btn:hover, .sub-navbar .add-product-btn.active { color: #f39c12; filter: drop-shadow(0 0 5px rgba(243, 156, 18, 0.4)); }
    .sub-navbar .add-product-btn:hover svg circle, .sub-navbar .add-product-btn.active svg circle { fill: rgba(243, 156, 18, 0.1); }
    .nav-indicator { position: absolute; bottom: 8px; left: 0; height: 3px; background-color: #f39c12; border-radius: 3px; box-shadow: 0 0 8px rgba(243, 156, 18, 0.6); transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1), width 0.4s ease; z-index: 1; width: 0; transform: translateX(0); }
    .nav-indicator.is-add { opacity: 0; }

    /* =========================================
       LIGHT MODE (AYDINLIK TEMA) CSS OVERRIDES
       ========================================= */
    body.light-mode { background-color: #f0f4f8; color: #1e293b; background-image: none; }
    body.light-mode .announcement-bar { background: #ffffff; border-bottom: 1px solid #e2e8f0; }
    body.light-mode .announcement-text { color: #3b82f6; }
    body.light-mode .navbar { background: rgba(255, 255, 255, 0.9); border-bottom: 1px solid #e2e8f0; }
    body.light-mode .nav-brand { color: #0f172a; }
    body.light-mode .nav-search input { background-color: #f8fafc; color: #1e293b; border-color: #cbd5e1; }
    body.light-mode .nav-search input:focus { border-color: #3b82f6; } 
    body.light-mode .nav-search button { background-color: #e2e8f0; color: #475569; border-color: #cbd5e1; }
    body.light-mode .nav-search button:hover { background-color: #cbd5e1; }
    body.light-mode .nav-icons a { color: #64748b; }
    body.light-mode .nav-icons a:hover { color: #3b82f6; } 
    body.light-mode .login-text-btn { color: #475569; }
    body.light-mode .login-text-btn:hover { color: #3b82f6; }
    body.light-mode .signup-btn { background: linear-gradient(135deg, #2563eb, #3b82f6); box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4); }
    body.light-mode .profile-avatar { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    body.light-mode .sub-navbar { background: rgba(255, 255, 255, 0.85); border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); }
    body.light-mode .sub-navbar a { color: #64748b; }
    body.light-mode .sub-navbar a:hover, body.light-mode .sub-navbar a.active { color: #0f172a; }
    body.light-mode .sub-navbar .add-product-btn { color: #64748b; }
    body.light-mode .sub-navbar .add-product-btn:hover, body.light-mode .sub-navbar .add-product-btn.active { color: #3b82f6; filter: drop-shadow(0 0 5px rgba(59, 130, 246, 0.4)); }
    body.light-mode .sub-navbar .add-product-btn:hover svg circle, body.light-mode .sub-navbar .add-product-btn.active svg circle { fill: rgba(59, 130, 246, 0.1); }
    body.light-mode .nav-indicator { background-color: #3b82f6; box-shadow: 0 0 8px rgba(59, 130, 246, 0.6); }
    body.light-mode .profile-dropdown { background-color: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
    body.light-mode .dropdown-header { border-bottom: 1px solid #e2e8f0; color: #64748b; }
    body.light-mode .dropdown-header strong { color: #0f172a; }
    body.light-mode .profile-dropdown a { color: #475569; }
    body.light-mode .profile-dropdown a:hover { background-color: #f1f5f9; color: #0f172a; }
    body.light-mode .dropdown-divider { background-color: #e2e8f0; }

    /* Sepet & Bildirim Light Mode Overrides */
    body.light-mode .cart-dropdown, body.light-mode .notif-dropdown { background-color: #ffffff; border-color: #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    body.light-mode .cart-header, body.light-mode .notif-header { border-color: #e2e8f0; color: #64748b; }
    body.light-mode .cart-item, body.light-mode .notif-item { border-color: #e2e8f0; }
    body.light-mode .cart-item-title, body.light-mode .notif-text { color: #0f172a; }
    body.light-mode .cart-item-price { color: #2563eb; }
    body.light-mode .cart-footer { background: #f8fafc; }
    body.light-mode .cart-total-row { color: #0f172a; }
    body.light-mode .cart-item-remove-btn { color: #ef4444; }
    body.light-mode .cart-item-remove-btn:hover { color: white; background: #ef4444; }
    body.light-mode .notif-item:hover { background: #f8fafc; }
    body.light-mode .notif-item.unread { background: rgba(59, 130, 246, 0.05); border-left-color: #3b82f6; }
    body.light-mode .cart-checkout-btn { background: #2563eb !important; color: #ffffff !important; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2); }
    body.light-mode .cart-checkout-btn:hover { background: #1d4ed8 !important; color: #ffffff !important; box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3); }
</style>

<div class="announcement-bar">
    <div class="announcement-text">🔥 DİJİTAL YARATICILIĞIN YENİ ADI SCOPE 🔥</div>
    <div class="announcement-text">💵 GELİRİNİ KATLAMAYA HAZIR OL 💵</div>
    <div class="announcement-text">💲PAKETLER İLE DAHA UCUZA AL💲</div>
    <div class="announcement-text">💡ÖZGÜRCE DÜŞÜN ÖZGÜRCE SAT💡</div>
    <div class="announcement-text">🤑FREELANCE İLE KENDİNZE ÖZEL ÜRÜN EDİN🤑</div>
</div>

<div class="sticky-header-container">
    <nav class="navbar">
        <div class="nav-brand-container">
            <a href="home" class="nav-brand">SCOPE</a>
        </div>

        <form class="nav-search" action="store" method="GET">
            <input type="text" name="search_query" placeholder="Sitede içerik ara..." required>
            <button type="submit">Ara</button>
        </form>

        <div class="nav-right-container">
            <div class="nav-icons">
                <a href="javascript:void(0)" id="themeToggleBtn" title="Tema Değiştir" style="margin-right: 15px;">
                    <svg id="themeIcon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </a>
            </div>

            <?php if($is_logged_in): ?>
                <div class="nav-icons">
                    <div class="cart-container" id="cartContainer" style="margin-right:15px;">
                        <a href="javascript:void(0)" id="cartBtn" title="Sepet">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <?php if(!empty($user_cart_ids)): ?>
                                <span class="icon-badge cart-color"><?php echo count($user_cart_ids); ?></span>
                            <?php endif; ?>
                        </a>

                        <div class="cart-dropdown" id="cartDropdown">
                            <div class="cart-header">Sepetiniz</div>
                            <div class="cart-items-wrapper">
                                <?php if(empty($cart_items)): ?>
                                    <div class="cart-empty">Sepetiniz boş.</div>
                                <?php else: ?>
                                    <?php 
                                    $display_count = 0;
                                    foreach($cart_items as $item): 
                                        if($display_count >= 3) break; 
                                        $display_count++;
                                    ?>
                                        <div class="cart-item">
                                            <div class="cart-item-img" style="background-image: url('<?php echo htmlspecialchars($item['cover_image'] ?? 'assets/images/default.jpg'); ?>');"></div>
                                            <div class="cart-item-info">
                                                <div class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                                <div class="cart-item-price"><?php echo htmlspecialchars($item['price_total']); ?> $</div>
                                            </div>
                                            <button onclick="removeFromCart('<?php echo $item['id']; ?>')" class="cart-item-remove-btn" title="Sepetten Çıkar">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if(count($cart_items) > 3): ?>
                                        <div class="cart-more">+<?php echo count($cart_items)-3; ?> ürün daha</div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="cart-footer">
                                <div class="cart-total-row"><span>Ara Toplam:</span><span><?php echo $cart_total; ?> $</span></div>
                                <a href="checkout" class="cart-checkout-btn">Ödemeye Git</a>
                            </div>
                        </div>
                    </div>

                    <a href="social" title="Mesajlar" style="margin-right:15px; position:relative;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <?php if($unread_messages > 0): ?>
                            <span class="icon-badge" style="background:#3498db;"><?php echo $unread_messages; ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="notif-container" id="notifContainer" style="margin-right:15px;">
                        <a href="javascript:void(0)" id="notifBtn" title="Bildirimler">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <?php if($unread_notifs > 0): ?>
                                <span class="icon-badge" id="notifBadge"><?php echo $unread_notifs; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-header"><span>Bildirimler</span></div>
                            <div class="notif-items-wrapper">
                                <?php if(empty($notifications)): ?>
                                    <div class="notif-empty">Hiç bildiriminiz yok.</div>
                                <?php else: ?>
                                    <?php foreach(array_slice($notifications, 0, 10) as $n): ?>
                                        <a href="<?php echo htmlspecialchars($n['link']); ?>" class="notif-item <?php echo $n['read'] ? '' : 'unread'; ?>">
                                            <span class="notif-text"><?php echo htmlspecialchars($n['text']); ?></span>
                                            <span class="notif-date"><?php echo date('d.m.Y H:i', strtotime($n['date'])); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-container">
                    <div class="profile-avatar" id="profileBtn">
                        <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'P'; ?>
                    </div>
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-header">
                            Signed in as <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></strong>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="profile/myaccount?tab=profile">Profile</a>
                        <a href="profile/myaccount?tab=public">Settings</a>
                        <a href="profile/myaccount?tab=assets">My Assets</a>
                        <div class="dropdown-divider"></div>
                        <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                            <a href="dashboard" style="color: #f39c12;">Admin Panel</a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <a href="?logout=true" style="color: #ff4a4a;">Sign out</a>
                    </div>
                </div>
            <?php else: ?>
                <a class="login-text-btn" onclick="openAuthModal('login')">Log in</a>
                <a class="signup-btn" onclick="openAuthModal('register')">Sign up</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="sub-nav-wrapper">
        <div class="sub-navbar" id="subNavbar">
            <a href="home" class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">Home</a>
            <a href="store" class="<?php echo ($current_page == 'store') ? 'active' : ''; ?>">Store</a>
            <a href="freelancehub" class="<?php echo ($current_page == 'freelancehub') ? 'active' : ''; ?>">Freelance</a>
            <a href="community" class="<?php echo ($current_page == 'community') ? 'active' : ''; ?>">Community</a>
            <?php if($is_logged_in): ?>
            <a href="createproduct" title="Ürün Ekle" class="add-product-btn <?php echo ($current_page == 'createproduct') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
            </a>
            <?php endif; ?>
            <div class="nav-indicator" id="navIndicator"></div>
        </div>
    </div>
</div>

<script>
    function removeFromCart(productId) {
        if(!confirm('Ürünü sepetinizden çıkarmak istediğinize emin misiniz?')) return;
        const formData = new FormData();
        formData.append('action', 'remove_from_cart');
        formData.append('product_id', productId);
        fetch('index.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => { if(data.status === 'success') { window.location.reload(); } })
        .catch(error => console.error('Hata:', error));
    }

    document.addEventListener("DOMContentLoaded", function() {
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const moonPath = 'M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z';
        const sunPaths = '<circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>';

        if (localStorage.getItem('theme') === 'light') { themeIcon.innerHTML = sunPaths; }
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle('light-mode');
                document.documentElement.classList.toggle('light-mode');
                if (document.body.classList.contains('light-mode')) {
                    localStorage.setItem('theme', 'light'); themeIcon.innerHTML = sunPaths;
                } else {
                    localStorage.setItem('theme', 'dark'); themeIcon.innerHTML = `<path d="${moonPath}"></path>`;
                }
            });
        }

        const announcements = document.querySelectorAll('.announcement-text');
        if (announcements.length > 0) {
            let currentAnnounce = 0; announcements[currentAnnounce].classList.add('show');
            if (announcements.length > 1) {
                setInterval(() => {
                    const prevAnnounce = currentAnnounce; currentAnnounce = (currentAnnounce + 1) % announcements.length;
                    announcements[prevAnnounce].classList.remove('show'); announcements[prevAnnounce].classList.add('exit');
                    announcements[currentAnnounce].classList.remove('exit'); announcements[currentAnnounce].classList.add('show');
                    setTimeout(() => { announcements[prevAnnounce].classList.remove('exit'); }, 500); 
                }, 3500); 
            }
        }

        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        const cartBtn = document.getElementById('cartBtn');
        const cartDropdown = document.getElementById('cartDropdown');
        const cartContainer = document.getElementById('cartContainer');
        const notifBtn = document.getElementById('notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');
        const notifContainer = document.getElementById('notifContainer');
        const notifBadge = document.getElementById('notifBadge');

        function closeAllDropdowns() {
            if(profileDropdown) profileDropdown.classList.remove('open');
            if(cartDropdown) cartDropdown.classList.remove('open');
            if(notifDropdown) notifDropdown.classList.remove('open');
        }

        if (profileBtn) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation(); const isOpen = profileDropdown.classList.contains('open');
                closeAllDropdowns(); if(!isOpen) profileDropdown.classList.add('open');
            });
        }
        
        if (cartBtn) {
            cartBtn.addEventListener('click', function(e) {
                e.stopPropagation(); const isOpen = cartDropdown.classList.contains('open');
                closeAllDropdowns(); if(!isOpen) cartDropdown.classList.add('open');
            });
        }

        if (notifBtn) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation(); const isOpen = notifDropdown.classList.contains('open');
                closeAllDropdowns();
                if(!isOpen) {
                    notifDropdown.classList.add('open');
                    if (notifBadge) {
                        fetch('index.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=read_notifications' })
                        .then(() => { notifBadge.style.display = 'none'; });
                    }
                }
            });
        }

        document.addEventListener('click', function(e) {
            if (profileBtn && !profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) { profileDropdown.classList.remove('open'); }
            if (cartContainer && !cartContainer.contains(e.target) && cartDropdown) { cartDropdown.classList.remove('open'); }
            if (notifContainer && !notifContainer.contains(e.target) && notifDropdown) { notifDropdown.classList.remove('open'); }
        });

        const subNavbar = document.getElementById('subNavbar');
        const items = subNavbar.querySelectorAll('a');
        const indicator = document.getElementById('navIndicator');
        let activeItem = subNavbar.querySelector('a.active');

        function setIndicator(element, animate = true) {
            if (!element) return;
            items.forEach(i => i.classList.remove('active'));
            element.classList.add('active'); activeItem = element;
            if (element.classList.contains('add-product-btn')) {
                indicator.classList.add('is-add'); indicator.style.width = '0px'; return;
            } else { indicator.classList.remove('is-add'); }

            const indicatorWidth = 18; 
            const offsetLeft = element.offsetLeft + (element.offsetWidth / 2) - (indicatorWidth / 2);
            if (!animate) { indicator.style.transition = 'none'; }
            indicator.style.width = `${indicatorWidth}px`; indicator.style.transform = `translateX(${offsetLeft}px)`;
            if (!animate) {
                void indicator.offsetWidth; 
                indicator.style.transition = 'transform 0.4s cubic-bezier(0.25, 1, 0.5, 1), width 0.4s ease';
            }
        }
        if (activeItem) { setIndicator(activeItem, false); }
    });
</script>