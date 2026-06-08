<?php
session_start();

require_once __DIR__ . '/src/utils/formatters.php';
require_once __DIR__ . '/src/utils/roles.php';
require_once __DIR__ . '/src/utils/validators.php';

$db_file = __DIR__ . '/backend/database/users_data.json';
$product_db = __DIR__ . '/backend/database/product_data.json';

// --- ÇIKIŞ YAPMA İŞLEMİ ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: home"); exit;
}

// --- POST İŞLEMLERİ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!file_exists($db_file)) {
        if (!is_dir(dirname($db_file))) { mkdir(dirname($db_file), 0777, true); }
        file_put_contents($db_file, json_encode([]));
    }
    $users = json_decode(file_get_contents($db_file), true) ?: [];
    $action = $_POST['action'];

    if ($action === 'register') {
        $username = trim($_POST['username']); $email = trim($_POST['email']); $password = $_POST['password'];
        $exists = false;
        foreach ($users as $user) { if ($user['email'] === $email || $user['username'] === $username) { $exists = true; break; } }
        if ($exists) { echo "<script>alert('Bu email veya kullanıcı adı zaten kayıtlı!');</script>"; } 
        else {
            $new_user = ['id' => uniqid(), 'username' => $username, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'user', 'created_at' => date('Y-m-d H:i:s')];
            $users[] = $new_user;
            file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
            $_SESSION['is_logged_in'] = true; $_SESSION['user_id'] = $new_user['id']; $_SESSION['username'] = $new_user['username']; $_SESSION['role'] = 'user'; $_SESSION['is_admin'] = false;
            header("Location: home"); exit;
        }
    }

    if ($action === 'login') {
        $login_id = trim($_POST['login_id']); $password = $_POST['password'];
        if ($login_id === 'root@admin' && $password === 'admin') {
            $_SESSION['is_logged_in'] = true; $_SESSION['user_id'] = 'ADMIN_ROOT'; $_SESSION['username'] = 'Sistem Yöneticisi'; $_SESSION['role'] = 'admin'; $_SESSION['is_admin'] = true;
            header("Location: dashboard"); exit;
        }
        foreach ($users as $user) {
            if ($user['email'] === $login_id || $user['username'] === $login_id) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['is_logged_in'] = true; $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'user'; $_SESSION['is_admin'] = ($_SESSION['role'] === 'admin');
                    header("Location: home"); exit;
                }
            }
        }
        echo "<script>alert('Giriş bilgileri hatalı!');</script>";
    }

    // --- PROFİL GÜNCELLEME İŞLEMİ ---
    if ($action === 'update_profile') {
        $current_user_id = $_SESSION['user_id'];
        $new_username = trim($_POST['username']);
        
        foreach ($users as &$user) {
            if ($user['id'] === $current_user_id) {
                $user['username'] = $new_username;
                $_SESSION['username'] = $new_username;

                $avatar_dir = "pictures/avatars/";
                $banner_dir = "pictures/banner/";

                if (!is_dir($avatar_dir)) mkdir($avatar_dir, 0777, true);
                if (!is_dir($banner_dir)) mkdir($banner_dir, 0777, true);

                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $avatar_name = "avatar_" . $user['id'] . "_" . time() . "." . $ext;
                    $target_path = $avatar_dir . $avatar_name;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) { $user['avatar'] = $target_path; }
                }

                if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
                    $banner_name = "banner_" . $user['id'] . "_" . time() . "." . $ext;
                    $target_path = $banner_dir . $banner_name;
                    if (move_uploaded_file($_FILES['banner']['tmp_name'], $target_path)) { $user['banner'] = $target_path; }
                }
                break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        header("Location: profile/myaccount?tab=public&success=1");
        exit;
    }

    // --- YENİ ÜRÜN OLUŞTURMA ---
    if ($action === 'create_product') {
        $products = file_exists($product_db) ? json_decode(file_get_contents($product_db), true) ?: [] : [];
        $id = uniqid(); 
        $base_dir = __DIR__ . '/backend/database/Products/' . $id;
        $images_dir = $base_dir . '/Images';
        $files_dir = $base_dir . '/Files';
        
        if (!is_dir($images_dir)) mkdir($images_dir, 0777, true);
        if (!is_dir($files_dir)) mkdir($files_dir, 0777, true);

        $cover_data = null;
        if (isset($_FILES['cover_image_file']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['cover_image_file']['name'], PATHINFO_EXTENSION);
            $target = $images_dir . '/title_image.' . $ext;
            if(move_uploaded_file($_FILES['cover_image_file']['tmp_name'], $target)) {
                $cover_data = 'backend/database/Products/' . $id . '/Images/title_image.' . $ext;
            }
        }

        $gallery_paths = [];
        if (isset($_FILES['gallery_images'])) {
            foreach ($_FILES['gallery_images']['name'] as $i => $name) {
                if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $filename = 'gorsel_' . ($i + 1) . '.' . $ext;
                    $target = $images_dir . '/' . $filename;
                    if(move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $target)) {
                        $gallery_paths[] = 'backend/database/Products/' . $id . '/Images/' . $filename;
                    }
                }
            }
        }

        $product_file_path = null;
        if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
            $filename = $_FILES['product_file']['name'];
            $target = $files_dir . '/' . $filename;
            if(move_uploaded_file($_FILES['product_file']['tmp_name'], $target)) {
                $product_file_path = 'backend/database/Products/' . $id . '/Files/' . $filename;
            }
        }

        $new_product = [
            'id'              => $id,
            'name'            => $_POST['product_name'] ?? '',
            'description'     => $_POST['product_detail'] ?? '',
            'type'            => $_POST['asset_type'] ?? '', 
            'price_total'     => (float)($_POST['price_total'] ?? 0),
            'price_seller'    => (float)($_POST['price_seller'] ?? 0),
            'seller_username' => $_SESSION['username'] ?? 'Unknown',
            'seller_id'       => $_SESSION['user_id'] ?? '',
            'cover_image'     => $cover_data,
            'gallery'         => $gallery_paths,
            'product_file'    => $product_file_path,
            'units_sold'      => 0,
            'reviews'         => [],
            'created_at'      => date('Y-m-d H:i:s')
        ];

        $products[] = $new_product;
        file_put_contents($product_db, json_encode($products, JSON_PRETTY_PRINT));
        
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (!isset($u['assets'])) $u['assets'] = [];
                if (!in_array($id, $u['assets'])) { $u['assets'][] = $id; }
                break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        echo "success";
        exit;
    }

    // --- ÜRÜN DÜZENLEME/GÜNCELLEME ---
    if ($action === 'edit_product' && isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        $products = file_exists($product_db) ? json_decode(file_get_contents($product_db), true) ?: [] : [];
        $updated = false;

        foreach ($products as &$p) {
            if ($p['id'] === $product_id && $p['seller_id'] === $_SESSION['user_id']) {
                $p['name'] = $_POST['product_name'] ?? $p['name'];
                $p['description'] = $_POST['product_detail'] ?? $p['description'];
                $p['type'] = $_POST['asset_type'] ?? $p['type'];
                $p['price_total'] = (float)($_POST['price_total'] ?? $p['price_total']);
                $p['price_seller'] = (float)($_POST['price_seller'] ?? $p['price_seller']);

                $base_dir = __DIR__ . '/backend/database/Products/' . $product_id;
                $images_dir = $base_dir . '/Images';
                $files_dir = $base_dir . '/Files';

                // Klasörler yoksa oluştur
                if (!is_dir($images_dir)) mkdir($images_dir, 0777, true);
                if (!is_dir($files_dir)) mkdir($files_dir, 0777, true);

                // Yeni kapak resmi yüklendiyse
                if (isset($_FILES['cover_image_file']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['cover_image_file']['name'], PATHINFO_EXTENSION);
                    $target = $images_dir . '/title_image.' . $ext;
                    if(move_uploaded_file($_FILES['cover_image_file']['tmp_name'], $target)) {
                        $p['cover_image'] = 'backend/database/Products/' . $product_id . '/Images/title_image.' . $ext;
                    }
                }
                
                // YENİ: Ürün Dosyası (Satılan Varlık) yüklendiyse
                if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
                    $filename = $_FILES['product_file']['name'];
                    $target = $files_dir . '/' . $filename;
                    if(move_uploaded_file($_FILES['product_file']['tmp_name'], $target)) {
                        $p['product_file'] = 'backend/database/Products/' . $product_id . '/Files/' . $filename;
                    }
                }

                $updated = true;
                break;
            }
        }

        if ($updated) {
            file_put_contents($product_db, json_encode($products, JSON_PRETTY_PRINT));
            echo "success";
        } else {
            echo "error";
        }
        exit;
    }

    // --- SEPET İŞLEMLERİ ---
    if ($action === 'add_to_cart' && isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (!isset($u['cart'])) $u['cart'] = [];
                if (!in_array($product_id, $u['cart'])) { $u['cart'][] = $product_id; }
                break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success']); 
        exit;
    }

    if ($action === 'remove_from_cart' && isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (isset($u['cart'])) {
                    $u['cart'] = array_values(array_filter($u['cart'], function($id) use ($product_id) {
                        return $id !== $product_id;
                    }));
                }
                break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success']); 
        exit;
    }

    if ($action === 'checkout' && isset($_SESSION['user_id'])) {
        $products = file_exists($product_db) ? json_decode(file_get_contents($product_db), true) ?: [] : [];
        $products_updated = false;

        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (!isset($u['assets'])) $u['assets'] = []; 
                if (isset($u['cart']) && is_array($u['cart'])) {
                    foreach ($u['cart'] as $cart_item) {
                        if (!in_array($cart_item, $u['assets'])) {
                            $u['assets'][] = $cart_item; 
                            foreach ($products as &$p) {
                                if ($p['id'] === $cart_item) {
                                    $p['units_sold'] = isset($p['units_sold']) ? (int)$p['units_sold'] + 1 : 1;
                                    $products_updated = true;
                                    break;
                                }
                            }
                        }
                    }
                    $u['cart'] = []; 
                }
                break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        if ($products_updated) { file_put_contents($product_db, json_encode($products, JSON_PRETTY_PRINT)); }
        echo json_encode(['status' => 'success']); 
        exit;
    }

    // --- YORUM (REVIEW) GÖNDERME İŞLEMİ ---
    if ($action === 'submit_review' && isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);
        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];

        $is_owner = false;
        foreach ($users as $u) {
            if ($u['id'] === $user_id) {
                if (isset($u['assets']) && in_array($product_id, $u['assets'])) { $is_owner = true; }
                break;
            }
        }

        if (!$is_owner) {
            echo json_encode(['status' => 'error', 'message' => 'Yorum yapabilmek için ürünü satın almalısınız.']);
            exit;
        }

        $products = file_exists($product_db) ? json_decode(file_get_contents($product_db), true) ?: [] : [];
        $review_added = false;

        foreach ($products as &$p) {
            if ($p['id'] === $product_id) {
                if (!isset($p['reviews'])) $p['reviews'] = [];
                $already_reviewed = false;
                foreach ($p['reviews'] as $rev) {
                    if ($rev['user_id'] === $user_id) { $already_reviewed = true; break; }
                }

                if (!$already_reviewed) {
                    $p['reviews'][] = [
                        'user_id' => $user_id,
                        'username' => $username,
                        'rating' => $rating,
                        'comment' => $comment,
                        'date' => date('Y-m-d')
                    ];
                    $review_added = true;
                }
                break;
            }
        }

        if ($review_added) {
            file_put_contents($product_db, json_encode($products, JSON_PRETTY_PRINT));
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Bu ürün için zaten bir yorum yaptınız.']);
        }
        exit;
    }
}

require_once __DIR__ . '/src/services/adminApi.php';
$page = isset($_GET['page']) ? strtolower($_GET['page']) : 'home';
?>
<!DOCTYPE html>
<html lang="tr">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scope Projesi</title>
    <base href="<?php echo str_replace('index.php', '', $_SERVER['SCRIPT_NAME']); ?>">
    <style>
        body {
            background-color: #0d0d0f; color: #ffffff;
            background-image: radial-gradient(circle at 0% 0%, rgba(68, 17, 60, 0.3) 0%, transparent 40%), radial-gradient(circle at 100% 100%, rgba(145, 60, 30, 0.2) 0%, transparent 50%);
            background-attachment: fixed; margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .page-content { display: flex; justify-content: flex-start; align-items: center; min-height: calc(100vh - 70px); flex-direction: column; text-align: center; width: 100%; }
    </style>
</head>
<body>
    <?php include 'src/components/Navbar.php'; ?>
    <div class="page-content">
        <?php
        $allowed_pages = ['home', 'profile', 'store', 'freelancehub', 'createproduct', 'community', 'dashboard', 'usermanagement', 'assetapprovals', 'escrowdisputes', 'roles', 'productdetail', 'checkout', 'editproduct'];
        
        if (in_array($page, $allowed_pages)) {
            $admin_pages = ['dashboard', 'usermanagement', 'assetapprovals', 'escrowdisputes', 'roles'];
            if (in_array($page, $admin_pages)) {
                if (!canViewAdmin()) { echo "<script>alert('Yetkisiz Erişim!'); window.location.href='home';</script>"; exit; }
                $fileName = '';
                if($page == 'dashboard') $fileName = 'Dashboard';
                if($page == 'usermanagement') $fileName = 'UserManagement';
                if($page == 'assetapprovals') $fileName = 'AssetApprovals';
                if($page == 'escrowdisputes') $fileName = 'EscrowDisputes';
                if($page == 'roles') $fileName = 'Roles';
                include "src/pages/admin/" . $fileName . ".php";
            } else {
                $fileName = '';
                if ($page === 'freelancehub') $fileName = 'FreelanceHub';
                elseif ($page === 'createproduct') $fileName = 'CreateProduct';
                elseif ($page === 'editproduct') $fileName = 'EditProduct';
                else $fileName = ucfirst($page);
                
                include "src/pages/" . $fileName . ".php";
            }
        } else { include 'src/pages/Home.php'; }
        ?>
    </div>
    <?php include 'src/services/auth.php'; ?>
</body>
</html>