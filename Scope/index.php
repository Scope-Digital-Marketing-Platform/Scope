<?php
session_start();

require_once __DIR__ . '/src/utils/formatters.php';
require_once __DIR__ . '/src/utils/roles.php';
require_once __DIR__ . '/src/utils/validators.php';

$db_file = __DIR__ . '/backend/database/users_data.json';
$topic_db = __DIR__ . '/backend/database/topics_data.json';

// --- ÇIKIŞ YAPMA İŞLEMİ ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: home"); exit;
}

// --- GİRİŞ VE KAYIT İŞLEMLERİ ---
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
            $new_user = ['id' => uniqid(), 'username' => $username, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'user', 'created_at' => date('Y-m-d H:i:s'), 'created_topics' => [], 'replied_topics' => []];
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
        $product_db = __DIR__ . '/backend/database/product_data.json';
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
            'created_at'      => date('Y-m-d H:i:s')
        ];

        $products[] = $new_product;
        file_put_contents($product_db, json_encode($products, JSON_PRETTY_PRINT));
        
        echo "success";
        exit;
    }

    // --- SEPET İŞLEMLERİ (EKLE / ÇIKAR / CHECKOUT) ---
    // (Aynı kalacak, uzatmamak için gizlendi ama dosyanızda mevcut kısımlar)
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
        echo json_encode(['status' => 'success']); exit;
    }

    if ($action === 'remove_from_cart' && isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (isset($u['cart'])) {
                    $u['cart'] = array_values(array_filter($u['cart'], function($id) use ($product_id) { return $id !== $product_id; }));
                }
                break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success']); exit;
    }

    if ($action === 'checkout' && isset($_SESSION['user_id'])) {
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (!isset($u['assets'])) $u['assets'] = []; 
                if (isset($u['cart']) && is_array($u['cart'])) {
                    foreach ($u['cart'] as $cart_item) { if (!in_array($cart_item, $u['assets'])) { $u['assets'][] = $cart_item; } }
                    $u['cart'] = []; 
                }
                break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success']); exit;
    }

    // ==========================================
    // --- YENİ KONU OLUŞTURMA (COMMUNITY) ---
    // ==========================================
    if ($action === 'create_topic' && isset($_SESSION['user_id'])) {
        $topics = file_exists($topic_db) ? json_decode(file_get_contents($topic_db), true) ?: [] : [];
        $topic_id = uniqid('t_'); 
        $images_dir = __DIR__ . '/backend/database/Topics/' . $topic_id;
        
        if (!is_dir($images_dir)) mkdir($images_dir, 0777, true);

        $topic_images = [];
        if (isset($_FILES['topic_images'])) {
            foreach ($_FILES['topic_images']['name'] as $i => $name) {
                if ($_FILES['topic_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $filename = 'img_' . ($i + 1) . '_' . time() . '.' . $ext;
                    $target = $images_dir . '/' . $filename;
                    if(move_uploaded_file($_FILES['topic_images']['tmp_name'][$i], $target)) {
                        $topic_images[] = 'backend/database/Topics/' . $topic_id . '/' . $filename;
                    }
                }
            }
        }

        $new_topic = [
            'id' => $topic_id,
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'author_id' => $_SESSION['user_id'],
            'author_username' => $_SESSION['username'],
            'images' => $topic_images,
            'replies' => [],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $topics[] = $new_topic;
        file_put_contents($topic_db, json_encode($topics, JSON_PRETTY_PRINT));

        // Kullanıcı verisine ekle
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if(!isset($u['created_topics'])) $u['created_topics'] = [];
                $u['created_topics'][] = $topic_id;
                break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));

        header("Location: community");
        exit;
    }

    // ==========================================
    // --- KONUYA YANIT EKLEME ---
    // ==========================================
    if ($action === 'add_reply' && isset($_SESSION['user_id'])) {
        $topics = file_exists($topic_db) ? json_decode(file_get_contents($topic_db), true) ?: [] : [];
        $topic_id = $_POST['topic_id'];
        $message = trim($_POST['message']);

        if (!empty($message)) {
            foreach ($topics as &$t) {
                if ($t['id'] === $topic_id) {
                    $t['replies'][] = [
                        'user_id' => $_SESSION['user_id'],
                        'username' => $_SESSION['username'],
                        'message' => $message,
                        'date' => date('Y-m-d H:i:s')
                    ];
                    break;
                }
            }
            file_put_contents($topic_db, json_encode($topics, JSON_PRETTY_PRINT));

            // Kullanıcının yanıt verdiği farklı konuları takip et
            foreach ($users as &$u) {
                if ($u['id'] === $_SESSION['user_id']) {
                    if(!isset($u['replied_topics'])) $u['replied_topics'] = [];
                    if (!in_array($topic_id, $u['replied_topics'])) {
                        $u['replied_topics'][] = $topic_id;
                    }
                    break;
                }
            }
            file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        }
        header("Location: topicdetail?id=" . urlencode($topic_id));
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
        // createtopic ve topicdetail Eklendi
        $allowed_pages = ['home', 'profile', 'store', 'freelancehub', 'createproduct', 'community', 'createtopic', 'topicdetail', 'dashboard', 'usermanagement', 'assetapprovals', 'escrowdisputes', 'roles', 'productdetail', 'checkout'];
        
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
                if ($page === 'freelancehub') $fileName = 'FreelanceHub';
                elseif ($page === 'createproduct') $fileName = 'CreateProduct';
                elseif ($page === 'createtopic') $fileName = 'CreateTopic';
                elseif ($page === 'topicdetail') $fileName = 'TopicDetail';
                else $fileName = ucfirst($page);
                include "src/pages/" . $fileName . ".php";
            }
        } else { include 'src/pages/Home.php'; }
        ?>
    </div>
    <?php include 'src/services/auth.php'; ?>
</body>
</html>