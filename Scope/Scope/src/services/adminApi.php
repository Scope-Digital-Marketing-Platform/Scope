<?php
// JSON Dosya Yolları
$users_db = __DIR__ . '/../../backend/database/users_data.json';
$products_db = __DIR__ . '/../../backend/database/product_data.json';
$escrow_db = __DIR__ . '/../../backend/database/escrow_data.json';
$roles_db = __DIR__ . '/../../backend/database/roles_data.json';

if (!function_exists('getAdminData')) {
    function getAdminData($path) {
        if (!file_exists($path)) { file_put_contents($path, json_encode([])); }
        return json_decode(file_get_contents($path), true) ?: [];
    }
}

if (!function_exists('saveAdminData')) {
    function saveAdminData($path, $data) {
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}

// POST İşlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $action = $_POST['admin_action'];

    // ROL OLUŞTURMA
    if ($action === 'create_role') {
        $roles = getAllRoles();
        $roleName = trim($_POST['role_name']);
        $roleKey = strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $roleName));
        $roles[$roleKey] = ['label' => $roleName, 'permissions' => $_POST['permissions'] ?? []];
        saveAdminData($roles_db, $roles);
    } 
    // KULLANICIYA ROL ATAMA
    else if ($action === 'assign_role') {
        $users = getAdminData($users_db);
        foreach ($users as &$u) { if ($u['id'] === $_POST['user_id']) { $u['role'] = $_POST['new_role']; } }
        saveAdminData($users_db, $users);
    }
    // KULLANICI SİLME
    else if ($action === 'delete_user') {
        $users = getAdminData($users_db);
        $users = array_filter($users, fn($u) => $u['id'] !== $_POST['user_id']);
        saveAdminData($users_db, array_values($users));
    }
    // ASSET ONAY / RED
    else if ($action === 'approve_asset' || $action === 'reject_asset') {
        $products = getAdminData($products_db);
        foreach ($products as &$p) { if ($p['id'] === $_POST['asset_id']) { $p['status'] = ($action === 'approve_asset') ? 'approved' : 'rejected'; } }
        saveAdminData($products_db, $products);
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>

<style>
    /* ANA KONTEYNER: index.php'deki dikey hizalamayı kırar */
    .admin-container { 
        width: 100%; max-width: 1150px; margin: 20px auto; 
        text-align: left !important; display: block !important; 
    }
    
    /* NAV MENÜ: Hepsi yan yana */
    .admin-nav { 
        display: flex !important; flex-direction: row !important; 
        justify-content: center; gap: 12px; margin: 30px 0 50px 0; flex-wrap: wrap; 
    }
    .admin-nav a { 
        padding: 10px 18px; background: rgba(255,255,255,0.05); 
        border: 1px solid #333; border-radius: 8px; color: #aaa; 
        text-decoration: none; font-size: 14px; transition: 0.3s;
    }
    .admin-nav a:hover, .admin-nav a.active { background: linear-gradient(135deg, #a4452f, #702054); color: white; border-color: transparent; }

    /* İSTATİSTİK KARTLARI (GRID) */
    .admin-stats { 
        display: grid !important; 
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important; 
        gap: 20px; width: 100%;
    }
    .stat-card { 
        background: rgba(22, 22, 26, 0.7); padding: 35px 20px; 
        border-radius: 15px; border: 1px solid #2a2a30; text-align: center;
    }
    .stat-card h3 { font-size: 0.8rem; color: #888; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 1px; }
    .stat-card .value { font-size: 2.8rem; font-weight: 800; color: #fff; margin: 0; }
    
    /* Tablolar */
    .admin-table-wrapper { background: #16161a; border-radius: 12px; border: 1px solid #2a2a30; overflow: hidden; width: 100%; margin-top: 20px;}
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th { background: #1e1e24; color: #aaa; padding: 15px; text-align: left; }
    .admin-table td { padding: 15px; border-top: 1px solid #2a2a30; color: #ddd; }
    .btn-danger { background: #a4452f; border:none; padding: 5px 10px; border-radius: 4px; color: white; cursor: pointer; }
</style>