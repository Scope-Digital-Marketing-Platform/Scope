<?php
$roles = getAllRoles();
$available_permissions = [
    'view_admin' => 'Admin Panelini Görebilir',
    'edit_users' => 'Kullanıcıları Yönetebilir',
    'approve_assets' => 'Varlıkları Onaylayabilir',
    'resolve_disputes' => 'Anlaşmazlıkları Çözebilir',
    'buy_asset' => 'Satın Alım Yapabilir',
    'sell_asset' => 'Ürün Satabilir',
    'all_access' => 'TAM YETKİ (Süper Admin)'
];
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Rol ve Yetki Yönetimi</h1>
        <p>Sistemdeki rolleri tanımlayın ve hangi rolün neler yapabileceğini belirleyin.</p>
    </div>

    <div class="admin-nav">
    <a href="index.php?page=dashboard" class="<?php echo ($page == 'dashboard' ? 'active' : ''); ?>">📊 Dashboard</a>
    <a href="index.php?page=usermanagement" class="<?php echo ($page == 'usermanagement' ? 'active' : ''); ?>">👥 Kullanıcılar</a>
    <a href="index.php?page=roles" class="<?php echo ($page == 'roles' ? 'active' : ''); ?>">🛡️ Roller</a>
    <a href="index.php?page=assetapprovals" class="<?php echo ($page == 'assetapprovals' ? 'active' : ''); ?>">📦 Varlıklar</a>
    <a href="index.php?page=escrowdisputes" class="<?php echo ($page == 'escrowdisputes' ? 'active' : ''); ?>">⚖️ Anlaşmazlıklar</a>
</div>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; align-items: start;">
        
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Rol Adı (Key)</th>
                        <th>Görünen İsim</th>
                        <th>Yetkiler</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $key => $info): ?>
                    <tr>
                        <td><code style="color: #8a365c;"><?php echo $key; ?></code></td>
                        <td><strong><?php echo $info['label']; ?></strong></td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                <?php foreach ($info['permissions'] as $perm): ?>
                                    <span class="badge" style="background: rgba(255,255,255,0.05); font-size: 10px; color: #fff; padding: 2px 5px; border-radius: 4px;">
                                        <?php echo $perm; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($key !== 'admin' && $key !== 'user'): ?>
                                <form method="POST" onsubmit="return confirm('Bu rolü silmek istediğinize emin misiniz?');">
                                    <input type="hidden" name="admin_action" value="delete_role">
                                    <input type="hidden" name="role_key" value="<?php echo $key; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 4px 8px;">Sil</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #444; font-size: 12px;">Sistem Rolü</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="stat-card" style="text-align: left; background: #16161a; padding: 20px; border-radius: 12px; border: 1px solid #2a2a30;">
            <h3 style="color: #fff; margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 10px;">Yeni Rol Oluştur</h3>
            <form method="POST">
                <input type="hidden" name="admin_action" value="create_role">
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; color:#aaa; font-size:13px; margin-bottom:5px;">Rol İsmi (Örn: Moderatör)</label>
                    <input type="text" name="role_name" required style="width:100%; padding:10px; background:#0d0d0f; border:1px solid #333; color:white; border-radius:6px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; color:#aaa; font-size:13px; margin-bottom:10px;">Yetkiler Seçin</label>
                    <div style="max-height: 200px; overflow-y: auto; background: #0d0d0f; padding: 10px; border-radius: 6px; border: 1px solid #333;">
                        <?php foreach ($available_permissions as $p_key => $p_label): ?>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; cursor: pointer; font-size: 13px; color: #ddd;">
                                <input type="checkbox" name="permissions[]" value="<?php echo $p_key; ?>">
                                <?php echo $p_label; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%; padding: 12px; background: linear-gradient(90deg, #a4452f, #81254a); border: none; border-radius: 25px; color: white; cursor: pointer;">
                    Rolü Oluştur
                </button>
            </form>
        </div>

    </div>
</div>