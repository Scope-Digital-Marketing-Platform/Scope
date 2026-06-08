<?php
$users = json_decode(file_get_contents(__DIR__ . '/../../backend/database/users_data.json'), true) ?: [];
$user_data = null;
$is_own_profile = false;

$request_user = isset($_GET['user']) ? trim($_GET['user']) : null;

/* =========================
   PROFİL GÜNCELLEME
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'update_profile' &&
    isset($_SESSION['user_id'])
) {

    $db_file = __DIR__ . '/../../backend/database/users_data.json';
    $users = json_decode(file_get_contents($db_file), true) ?: [];

    $current_user_id = $_SESSION['user_id'];
    $new_username = trim($_POST['username']);

    foreach ($users as &$user) {
        if ($user['id'] === $current_user_id) {

            $user['username'] = $new_username;
            $_SESSION['username'] = $new_username;

            $avatar_dir = __DIR__ . '/../../pictures/avatars/';
            $banner_dir = __DIR__ . '/../../pictures/banner/';

            if (!is_dir($avatar_dir)) mkdir($avatar_dir, 0777, true);
            if (!is_dir($banner_dir)) mkdir($banner_dir, 0777, true);

            /* AVATAR */
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $avatar_name = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
                $target = $avatar_dir . $avatar_name;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                    $user['avatar'] = 'pictures/avatars/' . $avatar_name;
                }
            }

            /* BANNER */
            if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
                $banner_name = 'banner_' . $user['id'] . '_' . time() . '.' . $ext;
                $target = $banner_dir . $banner_name;

                if (move_uploaded_file($_FILES['banner']['tmp_name'], $target)) {
                    $user['banner'] = 'pictures/banner/' . $banner_name;
                }
            }

            break;
        }
    }

    file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));

    /* 🔥 SABİT REDIRECT (ARTIK HİÇ BOZULMAZ) */
    header("Location: /Scope/Scope/index.php?page=profile&user=myaccount&tab=public&success=1");
    exit;
}

/* =========================
   USER CHECK
========================= */
if ($request_user === 'myaccount' || empty($request_user)) {
    if (isset($_SESSION['user_id'])) {
        foreach ($users as $u) {
            if ($u['id'] === $_SESSION['user_id']) {
                $user_data = $u;
                $is_own_profile = true;
                break;
            }
        }
    }
} else {
    foreach ($users as $u) {
        if ($u['username'] === $request_user) {
            $user_data = $u;
            break;
        }
    }
}

if (!$user_data) {
    echo "<div style='text-align:center; padding:100px; color:white;'><h2>Kullanıcı bulunamadı.</h2></div>";
    exit;
}

/* =========================
   PRODUCTS
========================= */
$product_file_path = __DIR__ . '/../../backend/database/product_data.json';
$user_products = [];
$user_assets_products = [];
$user_assets_ids = $user_data['assets'] ?? [];

if (file_exists($product_file_path)) {
    $all_products = json_decode(file_get_contents($product_file_path), true) ?: [];

    foreach ($all_products as $product) {
        if (isset($product['seller_id']) && $product['seller_id'] === $user_data['id']) {
            $user_products[] = $product;
        }
        if (in_array($product['id'], $user_assets_ids)) {
            $user_assets_products[] = $product;
        }
    }
}

$avatar_url = $user_data['avatar'] ?? '';
$banner_url = $user_data['banner'] ?? '';
$active_tab = $_GET['tab'] ?? 'profile';
?>

<div class="profile-page-wrapper">

    <div class="profile-nav-sidebar">
        <button class="profile-nav-item <?= ($active_tab == 'profile') ? 'active' : '' ?>"
            onclick="switchTab('profile')">Profile</button>

        <?php if ($is_own_profile): ?>
            <button class="profile-nav-item <?= ($active_tab == 'public') ? 'active' : '' ?>"
                onclick="switchTab('public')">Public Profile</button>

            <button class="profile-nav-item <?= ($active_tab == 'account') ? 'active' : '' ?>"
                onclick="switchTab('account')">Account</button>

            <button class="profile-nav-item <?= ($active_tab == 'assets') ? 'active' : '' ?>"
                onclick="switchTab('assets')">My Assets</button>
        <?php endif; ?>
    </div>

    <div class="profile-content-area">

        <!-- PROFILE -->
        <div id="profile" class="tab-content <?= ($active_tab == 'profile') ? 'active' : '' ?>">

            <div class="profile-banner">
                <div class="profile-avatar-large"></div>
            </div>

            <div class="profile-info-header">
                <h2><?= htmlspecialchars($user_data['username']) ?></h2>
            </div>

            <div style="margin-top:40px;">
                <h3>Vitrin</h3>

                <div class="showcase-grid">

                    <?php if ($is_own_profile): ?>
                        <a href="/Scope/Scope/index.php?page=createproduct" class="add-product-btn">
                            Ürün Ekle
                        </a>
                    <?php endif; ?>

                    <?php foreach ($user_products as $product): ?>
                        <a href="/Scope/Scope/index.php?page=productdetail&id=<?= $product['id'] ?>"
                           class="product-card">

                            <div class="product-img"
                                 style="background-image:url('<?= $product['cover_image'] ?>')"></div>

                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        </a>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

        <!-- PUBLIC -->
        <?php if ($is_own_profile): ?>
        <div id="public" class="tab-content <?= ($active_tab == 'public') ? 'active' : '' ?>">

            <form method="POST" enctype="multipart/form-data" class="settings-form">
                <input type="hidden" name="action" value="update_profile">

                <input type="text" name="username"
                       value="<?= htmlspecialchars($user_data['username']) ?>">

                <input type="file" name="avatar">
                <input type="file" name="banner">

                <button class="save-btn">Save Changes</button>
            </form>

        </div>
        <?php endif; ?>

    </div>
</div>

<script>
function switchTab(tab) {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.location.href = url.toString();
}
</script>