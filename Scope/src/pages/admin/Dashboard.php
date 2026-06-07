<?php
// Verileri çek (adminApi.php içindeki fonksiyonu kullanır)
$users = getAdminData(__DIR__ . '/../../../backend/database/users_data.json');
$products = getAdminData(__DIR__ . '/../../../backend/database/product_data.json');

$pendingCount = 0;
if(is_array($products)) {
    foreach($products as $p) {
        if(!isset($p['status']) || $p['status'] == 'pending') $pendingCount++;
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Admin Control Center</h1>
        <p style="color: #888; margin-top: 10px;">Sistemin genel durumu ve istatistikleri</p>
    </div>

    <div class="admin-nav">
        <a href="index.php?page=dashboard" class="active">📊 Dashboard</a>
        <a href="index.php?page=usermanagement">👥 Kullanıcılar</a>
        <a href="index.php?page=roles">🛡️ Roller</a>
        <a href="index.php?page=assetapprovals">📦 Varlıklar</a>
        <a href="index.php?page=escrowdisputes">⚖️ Anlaşmazlıklar</a>
    </div>

    <div class="admin-stats">
        <div class="stat-card">
            <h3>Toplam Kullanıcı</h3>
            <p class="value"><?php echo is_array($users) ? count($users) : 0; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Yayındaki Varlıklar</h3>
            <p class="value"><?php echo is_array($products) ? (count($products) - $pendingCount) : 0; ?></p>
        </div>
        
        <div class="stat-card">
            <h3 style="color: #a4452f;">Onay Bekleyenler</h3>
            <p class="value"><?php echo $pendingCount; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Aktif Uyuşmazlıklar</h3>
            <p class="value">0</p>
        </div>
    </div>
</div>