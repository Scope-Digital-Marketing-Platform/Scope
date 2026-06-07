<?php
$users = getAdminData(__DIR__ . '/../../../backend/database/users_data.json');
$roles = getAllRoles();
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Kullanıcı Yönetimi</h1>
        <p>Platformdaki tüm kullanıcıları görüntüle ve rollerini yönet.</p>
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
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" style="text-align: center;">Henüz kullanıcı yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><span style="color:#666;">#<?php echo substr($user['id'], 0, 8); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="admin_action" value="assign_role">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="new_role" onchange="this.form.submit()" style="background:#1a1a1e; color:white; border:1px solid #333; padding:5px; border-radius:4px; cursor:pointer;">
                                    <?php foreach ($roles as $key => $roleInfo): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($user['role']) && $user['role'] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $roleInfo['label']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <form method="POST" class="action-form" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="admin_action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-danger">Sil</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>