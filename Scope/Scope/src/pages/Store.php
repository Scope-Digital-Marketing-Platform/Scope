<?php
// JSON dosyasının doğru fiziksel yolu
$json_file_path = __DIR__ . '/../../backend/database/product_data.json';

$products = [];
if (file_exists($json_file_path)) {
    $json_data = file_get_contents($json_file_path);
    $products = json_decode($json_data, true) ?: []; 
}

// --- KULLANICI SEPET VE VARLIK (ASSETS) KONTROLÜ ---
$user_cart = [];
$user_assets = [];
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true && isset($_SESSION['user_id'])) {
    $users_db_path = __DIR__ . '/../../backend/database/users_data.json';
    if(file_exists($users_db_path)){
        $u_db = json_decode(file_get_contents($users_db_path), true) ?: [];
        foreach($u_db as $usr) {
            if($usr['id'] === $_SESSION['user_id']) {
                $user_cart = $usr['cart'] ?? [];
                $user_assets = $usr['assets'] ?? [];
                break;
            }
        }
    }
}

// --- FİLTRELEME PARAMETRELERİNİ ALMA ---
$tags_str = isset($_GET['tags']) ? trim(strtolower($_GET['tags'])) : '';
$tags_array = $tags_str !== '' ? array_filter(array_map('trim', explode(',', $tags_str))) : [];

// Navbar'dan gelen global aramayı (search_query) filtre etiketlerine bağlıyoruz
$search_query = isset($_GET['search_query']) ? trim(strtolower($_GET['search_query'])) : '';
if ($search_query !== '') {
    $tags_array[] = $search_query;
}
$tags_array = array_unique($tags_array);

$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float)$_GET['min_price'] : null;
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float)$_GET['max_price'] : null;
$is_free = isset($_GET['free']) && $_GET['free'] === 'on';

// --- SAYFALAMA DEĞİŞKENLERİ ---
$items_per_page = 20;
$current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;

$filtered_products = [];

// --- FİLTRELEME MANTIĞI ---
foreach ($products as $product) {
    if (empty($product['name'])) continue;

    if (!empty($tags_array)) {
        $p_name = strtolower($product['name']);
        $match = false;
        foreach ($tags_array as $tag) {
            if (strpos($p_name, $tag) !== false) { 
                $match = true; 
                break; 
            }
        }
        if (!$match) continue; 
    }

    if ($filter_type !== '') {
        if (!isset($product['type']) || $product['type'] !== $filter_type) continue;
    }

    $price = (float)($product['price_total'] ?? 0);
    if ($is_free) {
        if ($price > 0) continue; 
    } else {
        if ($min_price !== null && $price < $min_price) continue;
        if ($max_price !== null && $price > $max_price) continue;
    }

    $filtered_products[] = $product;
}

// --- DİNAMİK SAYFALAMA HESAPLAMALARI ---
$total_products = count($filtered_products);
$total_pages = ceil($total_products / $items_per_page);

if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

$offset = ($current_page - 1) * $items_per_page;
$display_products = array_slice($filtered_products, $offset, $items_per_page);
?>

<style>
    body .page-content { padding: 0 !important; margin: 0 !important; }

    .store-layout { 
        display: flex; flex-direction: row; width: 100%; min-height: calc(100vh - 70px);
        align-items: stretch; text-align: left;
    }
    
    .store-sidebar { 
        width: 280px; flex-shrink: 0; position: relative; background-color: #16161a; 
        border-right: 1px solid rgba(255, 255, 255, 0.05); box-sizing: border-box; 
        margin-top: -45px; padding-top: 75px; padding-left: 25px; padding-right: 25px;
        padding-bottom: 30px; z-index: 1; transition: background-color 0.4s ease, border-color 0.4s ease;
    }

    .store-main { 
        flex-grow: 1; width: calc(100% - 280px); display: flex; flex-direction: column; 
        padding: 30px 40px; box-sizing: border-box;
    }

    .filter-title { color: white; font-size: 1.2rem; font-weight: bold; margin-bottom: 25px; border-bottom: 2px solid #2a2a30; padding-bottom: 10px; transition: color 0.4s ease, border-color 0.4s ease; }
    .filter-group { margin-bottom: 25px; display: flex; flex-direction: column; gap: 8px; }
    .filter-group label { color: #8b949e; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; transition: color 0.4s ease; }
    .filter-input, .filter-select { background: #0d0d0f; border: 1px solid #333; color: white; padding: 12px 15px; border-radius: 6px; outline: none; width: 100%; box-sizing: border-box; transition: all 0.3s; }
    .filter-input:focus, .filter-select:focus { border-color: #f39c12; }
    .price-row { display: flex; gap: 10px; }
    
    .checkbox-row { display: flex; justify-content: flex-end; align-items: center; gap: 8px; margin-top: 8px; cursor: pointer; }
    .checkbox-row span { color: #8b949e; font-size: 0.9rem; font-weight: bold; transition: color 0.4s ease; }
    .checkbox-row input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: #f39c12; }
    
    .apply-btn { background: #f39c12; color: black; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 10px; transition: all 0.3s ease; }
    .apply-btn:hover { opacity: 0.9; }
    
    .active-filters-area { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
    .filter-tag { background: transparent; border: 2px solid #f39c12; color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; font-weight: bold; transition: color 0.4s ease; }
    .filter-tag button { background: none; border: none; color: #f39c12; cursor: pointer; }
    
    .product-grid { 
        display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); 
        gap: 20px; width: 100%; margin-bottom: 40px; 
    }
    
    .product-card { position: relative; background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; transition: all 0.3s; text-decoration: none; }
    .product-card:hover { transform: translateY(-5px); border-color: #f39c12; }
    .product-img { width: 100%; height: 180px; background-color: #0d0d0f; background-size: cover; background-position: center; border-radius: 8px; margin-bottom: 15px; }
    .product-name { color: white; font-size: 1.1rem; margin: 0 0 10px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; transition: color 0.4s ease; }
    .product-price { color: #2ecc71; font-weight: bold; font-size: 1.1rem; margin: 0; }
    
    /* YENİ EKLENEN STİLLER (Sepet İkonu ve Sahip Olunan) */
    .product-price.owned-text { color: #f39c12; }
    .cart-indicator {
        position: absolute; top: 25px; right: 25px; background: #f39c12; color: #111;
        width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; 
        justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.5); z-index: 10;
    }

    body.light-mode .store-sidebar { background-color: #ffffff; border-right: 1px solid #e2e8f0; }
    body.light-mode .filter-title { color: #0f172a; }
    body.light-mode .filter-input, body.light-mode .filter-select { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .product-card { background: #ffffff; border: 1px solid #e2e8f0; }
    body.light-mode .product-name { color: #0f172a; }
    body.light-mode .product-price.owned-text { color: #2563eb; } /* Light mode için Sahip olunan mavi renk */
    body.light-mode .cart-indicator { background: #2563eb; color: white; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3); }
</style>

<div class="store-layout">
    <aside class="store-sidebar">
        <form id="filterForm" method="GET" action="store">
            <div class="filter-title">Filtreler</div>
            
            <div class="filter-group">
                <label>Kelime Ara</label>
                <input type="text" id="keywordInput" class="filter-input" placeholder="Kelime ekleyin... (Enter)">
                <input type="hidden" name="tags" id="tagsHidden" value="<?php echo htmlspecialchars(implode(',', $tags_array)); ?>">
            </div>
            
            <div class="filter-group">
                <label>Varlık Türü</label>
                <select name="type" class="filter-select">
                    <option value="">Tüm Türler</option>
                    <option value="3d_model" <?php echo $filter_type == '3d_model' ? 'selected' : ''; ?>>3D Model</option>
                    <option value="ui_kit" <?php echo $filter_type == 'ui_kit' ? 'selected' : ''; ?>>UI/UX Kit</option>
                    <option value="plugin" <?php echo $filter_type == 'plugin' ? 'selected' : ''; ?>>Yazılım & Plugin</option>
                    <option value="audio" <?php echo $filter_type == 'audio' ? 'selected' : ''; ?>>Ses & Müzik</option>
                    <option value="graphics" <?php echo $filter_type == 'graphics' ? 'selected' : ''; ?>>Grafik & Vektör</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Fiyat Aralığı ($)</label>
                <div class="price-row">
                    <input type="number" name="min_price" class="filter-input" placeholder="min" value="<?php echo $min_price !== null ? $min_price : ''; ?>">
                    <input type="number" name="max_price" class="filter-input" placeholder="max" value="<?php echo $max_price !== null ? $max_price : ''; ?>">
                </div>
                <label class="checkbox-row">
                    <span>Sadece Bedava</span>
                    <input type="checkbox" name="free" <?php echo $is_free ? 'checked' : ''; ?>>
                </label>
            </div>
            
            <button type="submit" class="apply-btn">Filtrele</button>
        </form>
    </aside>

    <main class="store-main">
        <?php if (!empty($tags_array)): ?>
            <div class="active-filters-area">
                <span class="label" style="color: #8b949e; font-size: 14px; font-weight: bold;">Aktif Filtreler:</span>
                <div class="tag-container" id="tagContainer" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php foreach($tags_array as $index => $tag): ?>
                        <div class="filter-tag">
                            <?php echo htmlspecialchars($tag); ?> 
                            <button type="button" onclick="removeTag('<?php echo htmlspecialchars($tag); ?>')">✖</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="product-grid">
            <?php foreach($display_products as $product): ?>
                <?php 
                $img = (!empty($product['cover_image'])) ? $product['cover_image'] : 'assets/images/default.jpg';
                // Kullanıcının sepetinde ve varlıklarında olup olmadığını kontrol ediyoruz
                $is_in_cart = in_array($product['id'], $user_cart);
                $is_owned = in_array($product['id'], $user_assets);
                ?>
                <a href="productdetail?id=<?php echo urlencode($product['id']); ?>" class="product-card">
                    
                    <?php if($is_in_cart && !$is_owned): ?>
                        <div class="cart-indicator" title="Sepetinizde">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    <?php endif; ?>

                    <div class="product-img" style="background-image: url('<?php echo htmlspecialchars($img); ?>');"></div>
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <?php if($is_owned): ?>
                        <p class="product-price owned-text">Sahip Olunan</p>
                    <?php else: ?>
                        <p class="product-price"><?php echo htmlspecialchars($product['price_total']); ?> $</p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<script>
    const filterForm = document.getElementById('filterForm');
    const keywordInput = document.getElementById('keywordInput');
    const tagsHidden = document.getElementById('tagsHidden');
    
    let currentTags = tagsHidden.value ? tagsHidden.value.split(',').map(t => t.trim()).filter(t => t !== '') : [];

    keywordInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value.trim().toLowerCase();
            if (val && !currentTags.includes(val)) {
                currentTags.push(val);
                tagsHidden.value = currentTags.join(',');
                keywordInput.value = ''; 
                filterForm.submit(); 
            }
        }
    });

    filterForm.addEventListener('submit', function(e) {
        const val = keywordInput.value.trim().toLowerCase();
        if (val && !currentTags.includes(val)) {
            currentTags.push(val);
            tagsHidden.value = currentTags.join(',');
            keywordInput.value = ''; 
        }
    });

    window.removeTag = function(tagToRemove) {
        currentTags = currentTags.filter(tag => tag !== tagToRemove);
        tagsHidden.value = currentTags.join(',');
        
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('search_query') && urlParams.get('search_query').toLowerCase() === tagToRemove) {
            urlParams.delete('search_query');
            urlParams.set('tags', tagsHidden.value);
            window.location.href = window.location.pathname + '?' + urlParams.toString();
            return;
        }
        
        filterForm.submit(); 
    }
</script>