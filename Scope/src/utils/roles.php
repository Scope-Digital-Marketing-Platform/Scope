<?php
/**
 * Tüm rolleri JSON dosyasından çeker.
 */
if (!function_exists('getAllRoles')) {
    function getAllRoles() {
        $path = __DIR__ . '/../../backend/database/roles_data.json';
        
        // Eğer klasör yoksa oluştur
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        if (!file_exists($path)) {
            $default = [
                "admin" => ["label" => "Sistem Yöneticisi", "permissions" => ["all_access"]],
                "user" => ["label" => "Standart Kullanıcı", "permissions" => ["buy_asset"]]
            ];
            file_put_contents($path, json_encode($default, JSON_PRETTY_PRINT));
            return $default;
        }
        return json_decode(file_get_contents($path), true) ?: [];
    }
}

/**
 * Yetki kontrolü
 */
if (!function_exists('hasPermission')) {
    function hasPermission($permission) {
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) return false;
        
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === 'ADMIN_ROOT') return true;

        $userRole = $_SESSION['role'] ?? 'user';
        $roles = getAllRoles();
        
        if (!isset($roles[$userRole])) return false;
        if (in_array('all_access', $roles[$userRole]['permissions'])) return true;
        
        return in_array($permission, $roles[$userRole]['permissions']);
    }
}

/**
 * Admin girişi kolay kontrol
 */
if (!function_exists('canViewAdmin')) {
    function canViewAdmin() {
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === 'ADMIN_ROOT') return true;
        return hasPermission('view_admin') || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
    }
}
?>