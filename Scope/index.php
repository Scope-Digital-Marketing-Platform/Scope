<?php
session_start();

require_once __DIR__ . '/src/utils/formatters.php';
require_once __DIR__ . '/src/utils/roles.php';
require_once __DIR__ . '/src/utils/validators.php';

$db_file = __DIR__ . '/backend/database/users_data.json';
$product_db = __DIR__ . '/backend/database/product_data.json';
$topic_db = __DIR__ . '/backend/database/topics_data.json';
$freelance_db = __DIR__ . '/backend/database/freelance_data.json';
$social_db = __DIR__ . '/backend/database/social_data.json';

function addNotification(&$users, $user_id, $text, $link) {
    foreach ($users as &$u) {
        if ($u['id'] === $user_id) {
            if (!isset($u['notifications'])) $u['notifications'] = [];
            array_unshift($u['notifications'], [
                'id' => uniqid('n_'),
                'text' => $text,
                'link' => $link,
                'read' => false,
                'date' => date('Y-m-d H:i:s')
            ]);
            break;
        }
    }
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: home"); exit; }

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
            $new_user = [
                'id' => uniqid(), 'username' => $username, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 
                'role' => 'user', 'created_at' => date('Y-m-d H:i:s'), 'created_topics' => [], 'replied_topics' => [], 'completed_jobs' => 0, 'notifications' => []
            ];
            $users[] = $new_user; file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
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

    if ($action === 'read_notifications' && isset($_SESSION['user_id'])) {
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if(isset($u['notifications'])) { foreach($u['notifications'] as &$n) { $n['read'] = true; } } break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT)); echo json_encode(['status' => 'success']); exit;
    }

    if ($action === 'update_profile') {
        $current_user_id = $_SESSION['user_id']; $new_username = trim($_POST['username']);
        foreach ($users as &$user) {
            if ($user['id'] === $current_user_id) {
                $user['username'] = $new_username; $_SESSION['username'] = $new_username;
                $avatar_dir = "pictures/avatars/"; $banner_dir = "pictures/banner/";
                if (!is_dir($avatar_dir)) mkdir($avatar_dir, 0777, true); if (!is_dir($banner_dir)) mkdir($banner_dir, 0777, true);
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION); $target_path = $avatar_dir . "avatar_" . $user['id'] . "_" . time() . "." . $ext;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) { $user['avatar'] = $target_path; }
                }
                if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION); $target_path = $banner_dir . "banner_" . $user['id'] . "_" . time() . "." . $ext;
                    if (move_uploaded_file($_FILES['banner']['tmp_name'], $target_path)) { $user['banner'] = $target_path; }
                } break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT)); header("Location: profile/myaccount?tab=public&success=1"); exit;
    }

    if ($action === 'create_product') {
        $products = file_exists($product_db) ? json_decode(file_get_contents($product_db), true) ?: [] : [];
        $id = uniqid(); $base_dir = __DIR__ . '/backend/database/Products/' . $id;
        $images_dir = $base_dir . '/Images'; $files_dir = $base_dir . '/Files';
        if (!is_dir($images_dir)) mkdir($images_dir, 0777, true); if (!is_dir($files_dir)) mkdir($files_dir, 0777, true);

        $cover_data = null; $gallery_paths = []; $product_file_path = null;
        if (isset($_FILES['cover_image_file']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['cover_image_file']['name'], PATHINFO_EXTENSION);
            if(move_uploaded_file($_FILES['cover_image_file']['tmp_name'], $images_dir . '/title_image.' . $ext)) { $cover_data = 'backend/database/Products/' . $id . '/Images/title_image.' . $ext; }
        }
        if (isset($_FILES['gallery_images'])) {
            foreach ($_FILES['gallery_images']['name'] as $i => $name) {
                if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION); $filename = 'gorsel_' . ($i + 1) . '.' . $ext;
                    if(move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $images_dir . '/' . $filename)) { $gallery_paths[] = 'backend/database/Products/' . $id . '/Images/' . $filename; }
                }
            }
        }
        if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
            $filename = $_FILES['product_file']['name'];
            if(move_uploaded_file($_FILES['product_file']['tmp_name'], $files_dir . '/' . $filename)) { $product_file_path = 'backend/database/Products/' . $id . '/Files/' . $filename; }
        }
        $products[] = [
            'id' => $id, 'name' => $_POST['product_name'] ?? '', 'description' => $_POST['product_detail'] ?? '', 'type' => $_POST['asset_type'] ?? '', 
            'price_total' => (float)($_POST['price_total'] ?? 0), 'price_seller' => (float)($_POST['price_seller'] ?? 0), 'seller_username' => $_SESSION['username'] ?? 'Unknown',
            'seller_id' => $_SESSION['user_id'] ?? '', 'cover_image' => $cover_data, 'gallery' => $gallery_paths, 'product_file' => $product_file_path,
            'created_at' => date('Y-m-d H:i:s'), 'units_sold' => 0, 'reviews' => []
        ];
        file_put_contents($product_db, json_encode($products, JSON_PRETTY_PRINT));
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (!isset($u['assets'])) $u['assets'] = [];
                if (!in_array($id, $u['assets'])) { $u['assets'][] = $id; } break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT)); echo "success"; exit;
    }

    if ($action === 'edit_product' && isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id']; $products = file_exists($product_db) ? json_decode(file_get_contents($product_db), true) ?: [] : []; $updated = false;
        foreach ($products as &$p) {
            if ($p['id'] === $product_id && $p['seller_id'] === $_SESSION['user_id']) {
                $p['name'] = $_POST['product_name'] ?? $p['name']; $p['description'] = $_POST['product_detail'] ?? $p['description'];
                $p['type'] = $_POST['asset_type'] ?? $p['type']; $p['price_total'] = (float)($_POST['price_total'] ?? $p['price_total']); $p['price_seller'] = (float)($_POST['price_seller'] ?? $p['price_seller']);
                $updated = true; break;
            }
        }
        if ($updated) { file_put_contents($product_db, json_encode($products, JSON_PRETTY_PRINT)); echo "success"; } else { echo "error"; } exit;
    }

    if ($action === 'add_to_cart' && isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (!isset($u['cart'])) $u['cart'] = [];
                if (!in_array($product_id, $u['cart'])) { $u['cart'][] = $product_id; } break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT)); echo json_encode(['status' => 'success']); exit;
    }

    if ($action === 'remove_from_cart' && isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (isset($u['cart'])) { $u['cart'] = array_values(array_filter($u['cart'], function($id) use ($product_id) { return $id !== $product_id; })); } break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT)); echo json_encode(['status' => 'success']); exit;
    }

    if ($action === 'checkout' && isset($_SESSION['user_id'])) {
        $products = file_exists($product_db) ? json_decode(file_get_contents($product_db), true) ?: [] : []; $products_updated = false;
        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if (!isset($u['assets'])) $u['assets'] = []; 
                if (isset($u['cart']) && is_array($u['cart'])) {
                    foreach ($u['cart'] as $cart_item) {
                        if (!in_array($cart_item, $u['assets'])) {
                            $u['assets'][] = $cart_item; 
                            foreach ($products as &$p) {
                                if ($p['id'] === $cart_item) { $p['units_sold'] = isset($p['units_sold']) ? (int)$p['units_sold'] + 1 : 1; $products_updated = true; break; }
                            }
                        }
                    }
                    $u['cart'] = []; 
                } break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        if ($products_updated) file_put_contents($product_db, json_encode($products, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success']); exit;
    }

    if ($action === 'create_topic' && isset($_SESSION['user_id'])) {
        $topics = file_exists($topic_db) ? json_decode(file_get_contents($topic_db), true) ?: [] : [];
        $topic_id = uniqid('t_'); $images_dir = __DIR__ . '/backend/database/Topics/' . $topic_id;
        if (!is_dir($images_dir)) mkdir($images_dir, 0777, true);
        $topic_images = [];
        if (isset($_FILES['topic_images'])) {
            foreach ($_FILES['topic_images']['name'] as $i => $name) {
                if ($_FILES['topic_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION); $filename = 'img_' . ($i + 1) . '_' . time() . '.' . $ext;
                    if(move_uploaded_file($_FILES['topic_images']['tmp_name'][$i], $images_dir . '/' . $filename)) { $topic_images[] = 'backend/database/Topics/' . $topic_id . '/' . $filename; }
                }
            }
        }
        $topics[] = [
            'id' => $topic_id, 'title' => trim($_POST['title'] ?? ''), 'content' => trim($_POST['content'] ?? ''),
            'author_id' => $_SESSION['user_id'], 'author_username' => $_SESSION['username'], 'images' => $topic_images,
            'replies' => [], 'created_at' => date('Y-m-d H:i:s')
        ];
        file_put_contents($topic_db, json_encode($topics, JSON_PRETTY_PRINT));

        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                if(!isset($u['created_topics'])) $u['created_topics'] = [];
                $u['created_topics'][] = $topic_id; break;
            }
        }
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT)); header("Location: community"); exit;
    }

    if ($action === 'edit_topic' && isset($_SESSION['user_id'])) {
        $topics = file_exists($topic_db) ? json_decode(file_get_contents($topic_db), true) ?: [] : [];
        $topic_id = $_POST['topic_id'];
        foreach ($topics as &$t) {
            if ($t['id'] === $topic_id && $t['author_id'] === $_SESSION['user_id']) {
                $t['title'] = trim($_POST['title']); $t['content'] = trim($_POST['content']); break;
            }
        }
        file_put_contents($topic_db, json_encode($topics, JSON_PRETTY_PRINT)); header("Location: topicdetail?id=" . urlencode($topic_id)); exit;
    }

    if ($action === 'delete_topic' && isset($_SESSION['user_id'])) {
        $topics = file_exists($topic_db) ? json_decode(file_get_contents($topic_db), true) ?: [] : [];
        $topic_id = $_POST['topic_id'];
        $topics = array_values(array_filter($topics, function($t) use ($topic_id) { return !($t['id'] === $topic_id && $t['author_id'] === $_SESSION['user_id']); }));
        file_put_contents($topic_db, json_encode($topics, JSON_PRETTY_PRINT)); header("Location: community"); exit;
    }

    if ($action === 'add_reply' && isset($_SESSION['user_id'])) {
        $topics = file_exists($topic_db) ? json_decode(file_get_contents($topic_db), true) ?: [] : [];
        $topic_id = $_POST['topic_id']; $message = trim($_POST['message']);
        if (!empty($message)) {
            foreach ($topics as &$t) {
                if ($t['id'] === $topic_id) { $t['replies'][] = ['user_id' => $_SESSION['user_id'], 'username' => $_SESSION['username'], 'message' => $message, 'date' => date('Y-m-d H:i:s')]; break; }
            }
            file_put_contents($topic_db, json_encode($topics, JSON_PRETTY_PRINT));
            foreach ($users as &$u) {
                if ($u['id'] === $_SESSION['user_id']) {
                    if(!isset($u['replied_topics'])) $u['replied_topics'] = [];
                    if (!in_array($topic_id, $u['replied_topics'])) { $u['replied_topics'][] = $topic_id; } break;
                }
            }
            file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        }
        header("Location: topicdetail?id=" . urlencode($topic_id)); exit;
    }

    if ($action === 'create_job' && isset($_SESSION['user_id'])) {
        $jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : [];
        $job_id = uniqid('job_'); 
        $jobs[] = [
            'id' => $job_id, 'title' => trim($_POST['title']), 'description' => trim($_POST['description']),
            'budget' => (float)$_POST['budget'], 'author_id' => $_SESSION['user_id'], 'author_username' => $_SESSION['username'],
            'status' => 'open', 'bids' => [], 'accepted_bid' => null, 'delivery' => null, 'created_at' => date('Y-m-d H:i:s')
        ];
        file_put_contents($freelance_db, json_encode($jobs, JSON_PRETTY_PRINT)); header("Location: freelancehub"); exit;
    }

    if ($action === 'edit_job' && isset($_SESSION['user_id'])) {
        $jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : [];
        $job_id = $_POST['job_id'];
        foreach ($jobs as &$j) {
            if ($j['id'] === $job_id && $j['author_id'] === $_SESSION['user_id'] && $j['status'] === 'open') {
                $j['title'] = trim($_POST['title']); $j['description'] = trim($_POST['description']); $j['budget'] = (float)$_POST['budget']; break;
            }
        }
        file_put_contents($freelance_db, json_encode($jobs, JSON_PRETTY_PRINT)); header("Location: jobdetail?id=" . urlencode($job_id)); exit;
    }

    if ($action === 'delete_job' && isset($_SESSION['user_id'])) {
        $jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : [];
        $job_id = $_POST['job_id'];
        $jobs = array_values(array_filter($jobs, function($j) use ($job_id) { return !($j['id'] === $job_id && $j['author_id'] === $_SESSION['user_id']); }));
        file_put_contents($freelance_db, json_encode($jobs, JSON_PRETTY_PRINT)); header("Location: freelancehub"); exit;
    }

    if ($action === 'place_bid' && isset($_SESSION['user_id'])) {
        $jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : [];
        $job_id = $_POST['job_id'];
        foreach ($jobs as &$job) {
            if ($job['id'] === $job_id && $job['status'] === 'open') {
                $job['bids'][] = ['user_id' => $_SESSION['user_id'], 'username' => $_SESSION['username'], 'amount' => (float)$_POST['amount'], 'message' => trim($_POST['message']), 'date' => date('Y-m-d H:i:s')];
                addNotification($users, $job['author_id'], "@{$_SESSION['username']} ilanınıza teklif verdi.", "jobdetail?id={$job_id}"); break;
            }
        }
        file_put_contents($freelance_db, json_encode($jobs, JSON_PRETTY_PRINT)); file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        header("Location: jobdetail?id=" . urlencode($job_id)); exit;
    }

    if ($action === 'accept_bid' && isset($_SESSION['user_id'])) {
        $jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : [];
        $job_id = $_POST['job_id']; $bidder_id = $_POST['bidder_id'];
        foreach ($jobs as &$job) {
            if ($job['id'] === $job_id && $job['author_id'] === $_SESSION['user_id'] && $job['status'] === 'open') {
                $job['status'] = 'in_progress';
                foreach ($job['bids'] as $bid) {
                    if ($bid['user_id'] === $bidder_id) {
                        $job['accepted_bid'] = $bid;
                        addNotification($users, $bidder_id, "Tebrikler! @{$job['author_username']} teklifinizi kabul etti. İşe başlayabilirsiniz.", "jobdetail?id={$job_id}"); break;
                    }
                } break;
            }
        }
        file_put_contents($freelance_db, json_encode($jobs, JSON_PRETTY_PRINT)); file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        header("Location: jobdetail?id=" . urlencode($job_id)); exit;
    }

    if ($action === 'deliver_job' && isset($_SESSION['user_id'])) {
        $jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : []; $job_id = $_POST['job_id'];
        $delivery_file_path = null;
        if (isset($_FILES['delivery_file']) && $_FILES['delivery_file']['error'] === UPLOAD_ERR_OK) {
            $dir = __DIR__ . '/backend/database/Freelance/' . $job_id; if (!is_dir($dir)) mkdir($dir, 0777, true);
            $ext = pathinfo($_FILES['delivery_file']['name'], PATHINFO_EXTENSION); $filename = 'delivery_' . time() . '.' . $ext;
            if(move_uploaded_file($_FILES['delivery_file']['tmp_name'], $dir . '/' . $filename)) { $delivery_file_path = 'backend/database/Freelance/' . $job_id . '/' . $filename; }
        }
        foreach ($jobs as &$job) {
            if ($job['id'] === $job_id && $job['status'] === 'in_progress' && $job['accepted_bid']['user_id'] === $_SESSION['user_id']) {
                $job['delivery'] = ['message' => trim($_POST['message']), 'file' => $delivery_file_path, 'date' => date('Y-m-d H:i:s')];
                addNotification($users, $job['author_id'], "@{$_SESSION['username']} işi teslim etti. Onayınız bekleniyor.", "jobdetail?id={$job_id}"); break;
            }
        }
        file_put_contents($freelance_db, json_encode($jobs, JSON_PRETTY_PRINT)); file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        header("Location: jobdetail?id=" . urlencode($job_id)); exit;
    }

    if ($action === 'approve_job' && isset($_SESSION['user_id'])) {
        $jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : []; $job_id = $_POST['job_id'];
        foreach ($jobs as &$job) {
            if ($job['id'] === $job_id && $job['author_id'] === $_SESSION['user_id'] && $job['status'] === 'in_progress') {
                $job['status'] = 'completed'; $freelancer_id = $job['accepted_bid']['user_id'];
                foreach ($users as &$u) {
                    if ($u['id'] === $freelancer_id) {
                        $u['completed_jobs'] = isset($u['completed_jobs']) ? $u['completed_jobs'] + 1 : 1;
                        addNotification($users, $freelancer_id, "@{$job['author_username']} teslimatınızı onayladı! Başarılı işler sayınız arttı.", "jobdetail?id={$job_id}"); break;
                    }
                } break;
            }
        }
        file_put_contents($freelance_db, json_encode($jobs, JSON_PRETTY_PRINT)); file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
        header("Location: jobdetail?id=" . urlencode($job_id)); exit;
    }

    // ==========================================
    // --- MESAJLAŞMA (SOCIAL) DÜZ MANTIK ---
    // ==========================================
    if ($action === 'send_message' && isset($_SESSION['user_id'])) {
        $social_data = file_exists($social_db) ? json_decode(file_get_contents($social_db), true) ?: [] : [];
        $receiver_username = trim($_POST['receiver_username']);
        $text = trim($_POST['message']);
        
        $receiver_id = null;
        foreach ($users as $u) { if ($u['username'] === $receiver_username) { $receiver_id = $u['id']; break; } }

        if ($receiver_id && $receiver_id !== $_SESSION['user_id']) {
            $file_path = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $dir = __DIR__ . '/backend/database/Social'; if (!is_dir($dir)) mkdir($dir, 0777, true);
                $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                $filename = 'msg_' . time() . '_' . uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . '/' . $filename)) { $file_path = 'backend/database/Social/' . $filename; }
            }

            if (!empty($text) || $file_path) {
                $social_data[] = [
                    'id' => uniqid('msg_'), 'sender_id' => $_SESSION['user_id'], 'sender_username' => $_SESSION['username'],
                    'receiver_id' => $receiver_id, 'receiver_username' => $receiver_username, 'message' => $text,
                    'file' => $file_path, 'read' => false, 'date' => date('Y-m-d H:i:s')
                ];
                file_put_contents($social_db, json_encode($social_data, JSON_PRETTY_PRINT));
                addNotification($users, $receiver_id, "@{$_SESSION['username']} sana yeni bir mesaj gönderdi.", "social?user=" . urlencode($_SESSION['username']));
                file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
            }
        }
        header("Location: social?user=" . urlencode($receiver_username)); exit;
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
        body { background-color: #0d0d0f; color: #ffffff; background-image: radial-gradient(circle at 0% 0%, rgba(68, 17, 60, 0.3) 0%, transparent 40%), radial-gradient(circle at 100% 100%, rgba(145, 60, 30, 0.2) 0%, transparent 50%); background-attachment: fixed; margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .page-content { display: flex; justify-content: flex-start; align-items: center; min-height: calc(100vh - 70px); flex-direction: column; text-align: center; width: 100%; }
    </style>
</head>
<body>
    <?php include 'src/components/Navbar.php'; ?>
    <div class="page-content">
        <?php
        $allowed_pages = [
            'home' => 'Home', 'profile' => 'Profile', 'store' => 'Store', 'social' => 'Social',
            'freelancehub' => 'FreelanceHub', 'createjob' => 'CreateJob', 'jobdetail' => 'JobDetail', 'editjob' => 'EditJob',
            'createproduct' => 'CreateProduct', 'editproduct' => 'EditProduct', 'productdetail' => 'ProductDetail',
            'community' => 'Community', 'createtopic' => 'CreateTopic', 'topicdetail' => 'TopicDetail', 'edittopic' => 'EditTopic',
            'checkout' => 'Checkout',
            'dashboard' => 'admin/Dashboard', 'usermanagement' => 'admin/UserManagement', 
            'assetapprovals' => 'admin/AssetApprovals', 'escrowdisputes' => 'admin/EscrowDisputes', 'roles' => 'admin/Roles'
        ];
        
        if (array_key_exists($page, $allowed_pages)) {
            $file_to_include = $allowed_pages[$page];
            if (strpos($file_to_include, 'admin/') === 0 && !canViewAdmin()) { echo "<script>alert('Yetkisiz Erişim!'); window.location.href='home';</script>"; exit; }
            include "src/pages/" . $file_to_include . ".php";
        } else { include 'src/pages/Home.php'; }
        ?>
    </div>
    <?php include 'src/services/auth.php'; ?>
</body>
</html>