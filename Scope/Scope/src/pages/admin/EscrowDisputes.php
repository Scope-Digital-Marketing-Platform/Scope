
<?php
$escrows = getAdminData(__DIR__ . '/../../../backend/database/escrow_data.json');

// Sadece 'disputed' (anlaşmazlık yaşanmış) işleri listele
$disputed_escrows = array_filter($escrows, function($e) {
    return isset($e['status']) && $e['status'] === 'disputed';
});
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Güvenli Ödeme Anlaşmazlıkları</h1>
        <p>İşveren ve Freelancer arasındaki anlaşmazlıklarda karar verin.</p>
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
                    <th>İş ID</th>
                    <th>İşveren</th>
                    <th>Freelancer</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Karar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($disputed_escrows)): ?>
                    <tr><td colspan="6" style="text-align: center;">Şu an aktif bir anlaşmazlık yok. Her şey yolunda! 🕊️</td></tr>
                <?php else: ?>
                    <?php foreach ($disputed_escrows as $escrow): ?>
                    <tr>
                        <td><span style="color:#666;">#<?php echo substr($escrow['id'], 0, 8); ?></span></td>
                        <td><strong><?php echo $escrow['buyer_id']; ?></strong></td>
                        <td><strong><?php echo $escrow['freelancer_id']; ?></strong></td>
                        <td><span style="color:#28a745; font-weight:bold;">$<?php echo $escrow['amount']; ?></span></td>
                        <td><span class="badge" style="background:rgba(220,53,69,0.1); color:#dc3545; border:1px solid rgba(220,53,69,0.3);">Anlaşmazlık</span></td>
                        <td>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="admin_action" value="resolve_dispute">
                                <input type="hidden" name="escrow_id" value="<?php echo $escrow['id']; ?>">
                                <input type="hidden" name="resolution" value="refunded">
                                <button type="submit" class="btn btn-danger" title="Parayı işverene iade et">İade Et (Buyer)</button>
                            </form>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="admin_action" value="resolve_dispute">
                                <input type="hidden" name="escrow_id" value="<?php echo $escrow['id']; ?>">
                                <input type="hidden" name="resolution" value="paid">
                                <button type="submit" class="btn btn-success" title="Parayı freelancer'a aktar">Öde (Freelancer)</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>