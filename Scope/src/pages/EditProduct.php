<?php
if (!isset($_SESSION['is_logged_in'])) { echo "<script>alert('Giriş yapmalısınız.'); window.location.href='home';</script>"; exit; }

$product_id = isset($_GET['id']) ? $_GET['id'] : '';
$product_db = __DIR__ . '/../../backend/database/product_data.json';
$products = file_exists($product_db) ? json_decode(file_get_contents($product_db), true) ?: [] : [];

$current_product = null;
foreach ($products as $p) {
    if ($p['id'] === $product_id && $p['seller_id'] === $_SESSION['user_id']) { $current_product = $p; break; }
}

if (!$current_product) { echo "<div style='padding:100px; color:white; text-align:center;'><h2>Yetkisiz erişim veya ürün bulunamadı.</h2></div>"; exit; }
?>

<style>
    .create-wrapper { max-width: 800px; width: 100%; margin: 40px auto; background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 40px; text-align: left; }
    .create-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #333; padding-bottom: 15px; margin-bottom: 30px; }
    .create-wrapper h1 { color: white; margin: 0; font-size: 1.8rem; }
    .form-group { margin-bottom: 25px; display: flex; flex-direction: column; gap: 8px; }
    .form-group label { color: #8b949e; font-weight: bold; font-size: 0.9rem; }
    .form-group input[type="text"], .form-group input[type="number"], .form-group textarea, .form-group select { background: #0d0d0f; border: 1px solid #333; color: white; padding: 15px; border-radius: 8px; outline: none; font-size: 1rem; width: 100%; box-sizing: border-box; }
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: #f39c12; }
    .form-group textarea { min-height: 150px; resize: vertical; }
    .submit-btn { background: #f39c12; color: #111; border: none; padding: 15px 30px; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: 0.3s; width: 100%; }
    .submit-btn:hover { background: #d68910; }
    .btn-danger { background: #ff4a4a; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; text-decoration: none;}
    .btn-danger:hover { background: #d63031; }

    body.light-mode .create-wrapper { background: #fff; border-color: #e2e8f0; }
    body.light-mode .create-header { border-color: #e2e8f0; }
    body.light-mode .create-wrapper h1 { color: #0f172a; }
    body.light-mode .form-group label { color: #64748b; }
    body.light-mode .form-group input, body.light-mode .form-group textarea, body.light-mode .form-group select { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .submit-btn { background: #3b82f6; color: white; }
    body.light-mode .submit-btn:hover { background: #2563eb; }
</style>

<div class="create-wrapper">
    <div class="create-header">
        <h1>Ürünü Düzenle</h1>
        
        <form method="POST" action="index.php" style="margin:0;">
            <input type="hidden" name="action" value="delete_product">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($current_product['id']); ?>">
            <button type="submit" class="btn-danger" onclick="return confirm('Bu ürünü mağazadan kalıcı olarak silmek istediğinize emin misiniz? (Satın alan kullanıcılar ürüne erişmeye devam eder, sadece vitrinden kaldırılır.)');">
                Ürünü Sil
            </button>
        </form>
    </div>

    <form action="index.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_product">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($current_product['id']); ?>">
        
        <div class="form-group">
            <label>Ürün Adı</label>
            <input type="text" name="product_name" required value="<?php echo htmlspecialchars($current_product['name']); ?>">
        </div>

        <div class="form-group">
            <label>Ürün Detayı</label>
            <textarea name="product_detail" required><?php echo htmlspecialchars($current_product['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Varlık Türü</label>
            <select name="asset_type" required>
                <option value="3d_model" <?php echo ($current_product['type'] == '3d_model') ? 'selected' : ''; ?>>3D Model</option>
                <option value="ui_kit" <?php echo ($current_product['type'] == 'ui_kit') ? 'selected' : ''; ?>>UI/UX Kit</option>
                <option value="plugin" <?php echo ($current_product['type'] == 'plugin') ? 'selected' : ''; ?>>Yazılım & Plugin</option>
                <option value="audio" <?php echo ($current_product['type'] == 'audio') ? 'selected' : ''; ?>>Ses & Müzik</option>
                <option value="graphics" <?php echo ($current_product['type'] == 'graphics') ? 'selected' : ''; ?>>Grafik & Vektör</option>
            </select>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Alıcı Fiyatı ($)</label>
                <input type="number" name="price_total" step="0.01" required value="<?php echo htmlspecialchars($current_product['price_total']); ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Satıcı Fiyatı ($)</label>
                <input type="number" name="price_seller" step="0.01" required value="<?php echo htmlspecialchars($current_product['price_seller']); ?>">
            </div>
        </div>

        <button type="submit" class="submit-btn">Değişiklikleri Kaydet</button>
    </form>
</div>