<?php
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "<script>window.location.href='home';</script>";
    exit;
}

$users_db = json_decode(file_get_contents(__DIR__ . '/../../backend/database/users_data.json'), true) ?: [];
$products_db = json_decode(file_get_contents(__DIR__ . '/../../backend/database/product_data.json'), true) ?: [];

$user_cart_ids = [];
foreach($users_db as $u) {
    if ($u['id'] === $_SESSION['user_id']) {
        $user_cart_ids = $u['cart'] ?? [];
        break;
    }
}

$cart_items = [];
$total_price = 0;
foreach($user_cart_ids as $cid) {
    foreach($products_db as $p) {
        if ($p['id'] === $cid) {
            $cart_items[] = $p;
            $total_price += (float)$p['price_total'];
            break;
        }
    }
}
?>

<style>
    .checkout-container { display: flex; gap: 40px; max-width: 1200px; width: 100%; margin: 40px auto; padding: 0 20px; align-items: flex-start; }
    .checkout-left { flex: 2; background: #16161a; border-radius: 16px; padding: 30px; border: 1px solid rgba(255,255,255,0.05); }
    .checkout-right { flex: 1; background: #16161a; border-radius: 16px; padding: 30px; border: 1px solid rgba(255,255,255,0.05); position: sticky; top: 100px; }
    
    .checkout-title { color: white; font-size: 1.8rem; margin-top: 0; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; }
    
    .cart-item-row { display: flex; gap: 20px; align-items: center; padding: 15px 0; border-bottom: 1px dashed rgba(255,255,255,0.1); position: relative; }
    .cart-item-img { width: 100px; height: 70px; border-radius: 8px; background-size: cover; background-position: center; background-color: #0d0d0f; }
    .cart-item-info { flex: 1; }
    .cart-item-title { color: white; font-size: 1.2rem; font-weight: bold; margin-bottom: 5px; }
    .cart-item-type { color: #8b949e; font-size: 0.9rem; }
    .cart-item-price { color: #f39c12; font-size: 1.3rem; font-weight: bold; margin-right: 20px;}

    /* Çıkarma Butonu Eklendi */
    .checkout-remove-btn { background: rgba(255, 74, 74, 0.1); border: 1px solid rgba(255, 74, 74, 0.3); color: #ff4a4a; width: 40px; height: 40px; border-radius: 8px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
    .checkout-remove-btn:hover { background: #ff4a4a; color: white; transform: translateY(-2px); }

    .summary-row { display: flex; justify-content: space-between; color: #cbd5e1; font-size: 1.1rem; margin-bottom: 15px; }
    .summary-total { display: flex; justify-content: space-between; color: white; font-size: 1.5rem; font-weight: bold; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
    
    .pay-btn { width: 100%; background: #2ecc71; color: white; border: none; padding: 18px; font-size: 1.2rem; font-weight: 800; border-radius: 12px; cursor: pointer; margin-top: 30px; transition: 0.3s; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3); }
    .pay-btn:hover { background: #27ae60; transform: translateY(-3px); box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4); }

    /* Açık Mod Kuralları */
    body.light-mode .checkout-left, body.light-mode .checkout-right { background: #ffffff; border-color: #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    body.light-mode .checkout-title { color: #0f172a; border-color: #e2e8f0; }
    body.light-mode .cart-item-title, body.light-mode .summary-total { color: #0f172a; }
    body.light-mode .cart-item-row { border-color: #e2e8f0; }
    body.light-mode .summary-row { color: #475569; }
    body.light-mode .cart-item-price { color: #2563eb; }
    
    @media (max-width: 900px) { .checkout-container { flex-direction: column; } .checkout-right { position: relative; top: 0; } }
</style>

<div class="checkout-container">
    <div class="checkout-left">
        <h2 class="checkout-title">Sipariş Özeti (<?php echo count($cart_items); ?> Ürün)</h2>
        
        <?php if(empty($cart_items)): ?>
            <p style="color: #8b949e;">Sepetinizde ürün bulunmamaktadır.</p>
        <?php else: ?>
            <?php foreach($cart_items as $item): ?>
                <div class="cart-item-row">
                    <div class="cart-item-img" style="background-image: url('<?php echo htmlspecialchars($item['cover_image'] ?? 'assets/images/default.jpg'); ?>');"></div>
                    <div class="cart-item-info">
                        <div class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="cart-item-type">Varlık: <?php echo htmlspecialchars($item['type']); ?></div>
                    </div>
                    <div class="cart-item-price"><?php echo htmlspecialchars($item['price_total']); ?> $</div>
                    
                    <!-- SİLME BUTONU -->
                    <button onclick="removeFromCart('<?php echo $item['id']; ?>')" class="checkout-remove-btn" title="Ürünü Çıkar">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="checkout-right">
        <h2 class="checkout-title">Ödeme Detayları</h2>
        
        <div class="summary-row">
            <span>Ara Toplam</span>
            <span><?php echo $total_price; ?> $</span>
        </div>
        <div class="summary-row">
            <span>İşlem Ücreti</span>
            <span>0.00 $</span>
        </div>
        
        <div class="summary-total">
            <span>Toplam</span>
            <span id="finalTotal"><?php echo $total_price; ?> $</span>
        </div>

        <?php if(!empty($cart_items)): ?>
            <button class="pay-btn" onclick="processCheckout()">Ödemeyi Tamamla</button>
        <?php else: ?>
            <button class="pay-btn" style="background:#555; cursor:not-allowed;" disabled>Sepet Boş</button>
        <?php endif; ?>
    </div>
</div>

<script>
    // --- ÇIKARMA FONKSİYONU ---
    function removeFromCart(productId) {
        if(!confirm('Ürünü sepetinizden çıkarmak istediğinize emin misiniz?')) return;
        
        const formData = new FormData();
        formData.append('action', 'remove_from_cart');
        formData.append('product_id', productId);

        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                window.location.reload(); 
            }
        })
        .catch(error => console.error('Hata:', error));
    }

    // --- ÖDEME FONKSİYONU ---
    function processCheckout() {
        const btn = document.querySelector('.pay-btn');
        btn.innerText = "İşleniyor...";
        btn.style.opacity = "0.7";

        const formData = new FormData();
        formData.append('action', 'checkout');

        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                alert("Ödeme başarılı! Ürünler varlıklarınıza (assets) eklendi.");
                window.location.href = "profile/myaccount?tab=assets"; 
            }
        })
        .catch(error => {
            console.error('Ödeme Hatası:', error);
            btn.innerText = "Ödemeyi Tamamla";
            btn.style.opacity = "1";
        });
    }
</script>