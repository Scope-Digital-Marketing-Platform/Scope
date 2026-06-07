
<?php
$products = getAdminData(__DIR__ . '/../../../backend/database/product_data.json');

// Sadece 'pending' (onay bekleyen) veya durumu olmayanları (varsayılan bekliyor) listele
$pending_products = array_filter($products, function($p) {
    return !isset($p['status']) || $p['status'] === 'pending';
});
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Varlık (Asset) Onayları</h1>
        <p>Geliştiricilerin mağazaya yüklediği ürünleri inceleyin.</p>
    </div>

    <div class="admin-nav">
    <a href="index.php?page=dashboard" class="<?php echo ($page == 'dashboard' ? 'active' : ''); ?>">📊 Dashboard</a>
    <a href="index.php?page=usermanagement" class="<?php echo ($page == 'usermanagement' ? 'active' : ''); ?>">👥 Kullanıcılar</a>
    <a href="index.php?page=roles" class="<?php echo ($page == 'roles' ? 'active' : ''); ?>">🛡️ Roller</a>
    <a href="index.php?page=assetapprovals" class="<?php echo ($page == 'assetapprovals' ? 'active' : ''); ?>">📦 Varlıklar</a>
    <a href="index.php?page=escrowdisputes" class="<?php echo ($page == 'escrowdisputes' ? 'active' : ''); ?>">⚖️ Anlaşmazlıklar</a>
</div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ürün Adı</th>
                    <th>Yükleyen (ID)</th>
                    <th>Fiyat</th>
                    <th>Tür</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_products)): ?>
                    <tr><td colspan="6" style="text-align: center;">Onay bekleyen varlık bulunmuyor. 🎉</td></tr>
                <?php else: ?>
                    <?php foreach ($pending_products as $asset): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars(isset($asset['title']) ? $asset['title'] : 'İsimsiz'); ?></strong></td>
                        <td><?php echo isset($asset['authorId']) ? substr($asset['authorId'], 0, 6) : '-'; ?></td>
                        <td>$<?php echo isset($asset['price']) ? $asset['price'] : '0.00'; ?></td>
                        <td><span class="badge" style="background:#333;color:#ddd;"><?php echo isset($asset['type']) ? $asset['type'] : 'Diğer'; ?></span></td>
                        <td><span class="badge pending">İnceleniyor</span></td>
                        <td>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="admin_action" value="approve_asset">
                                <input type="hidden" name="asset_id" value="<?php echo $asset['id']; ?>">
                                <button type="submit" class="btn btn-success">Onayla</button>
                            </form>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="admin_action" value="reject_asset">
                                <input type="hidden" name="asset_id" value="<?php echo $asset['id']; ?>">
                                <button type="submit" class="btn btn-danger">Reddet</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>