<?php
$topic_file_path = __DIR__ . '/../../backend/database/topics_data.json';
$users_db_path = __DIR__ . '/../../backend/database/users_data.json';

$topics = [];
if (file_exists($topic_file_path)) {
    $topics = json_decode(file_get_contents($topic_file_path), true) ?: [];
}
// Yeniden eskiye sırala
usort($topics, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });

// Arama Filtresi
$search_query = isset($_GET['q']) ? trim(strtolower($_GET['q'])) : '';
if (!empty($search_query)) {
    $topics = array_filter($topics, function($t) use ($search_query) {
        return strpos(strtolower($t['title']), $search_query) !== false;
    });
}

// Sağ Kutu İçin Aktif Kullanıcı Verileri
$user_created_count = 0;
$user_replied_count = 0;
if (isset($_SESSION['is_logged_in'])) {
    $users = file_exists($users_db_path) ? json_decode(file_get_contents($users_db_path), true) ?: [] : [];
    foreach ($users as $u) {
        if ($u['id'] === $_SESSION['user_id']) {
            $user_created_count = count($u['created_topics'] ?? []);
            $user_replied_count = count($u['replied_topics'] ?? []);
            break;
        }
    }
}
?>

<style>
    body .page-content { padding: 0 !important; margin: 0 !important; }
    .com-layout { display: flex; gap: 30px; max-width: 1200px; width: 100%; margin: 40px auto; padding: 0 20px; align-items: flex-start; text-align: left; }
    .com-main { flex: 3; display: flex; flex-direction: column; gap: 20px; }
    .com-sidebar { flex: 1; min-width: 280px; position: sticky; top: 90px; }

    /* Arama ve Başlık Kısmı */
    .com-header { display: flex; justify-content: space-between; align-items: center; background: #1a1a1e; padding: 20px; border-radius: 12px; border: 1px solid #333; }
    .search-bar { display: flex; gap: 10px; width: 100%; max-width: 400px; }
    .search-bar input { flex:1; background: #0d0d0f; border: 1px solid #333; color: white; padding: 12px 15px; border-radius: 8px; outline: none; }
    .search-bar input:focus { border-color: #f39c12; }
    .search-btn { background: #333; color: white; border: none; padding: 0 20px; border-radius: 8px; cursor: pointer; transition: 0.3s; }
    .search-btn:hover { background: #f39c12; color: #111; }

    /* Konu Kartları */
    .topic-card { background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; text-decoration: none; transition: all 0.3s; }
    .topic-card:hover { transform: translateX(5px); border-color: #f39c12; background: rgba(243, 156, 18, 0.03); }
    .topic-info h3 { color: white; font-size: 1.2rem; margin: 0 0 10px 0; }
    .topic-meta { color: #8b949e; font-size: 0.85rem; display: flex; gap: 15px; }
    .topic-stats { text-align: center; color: #8b949e; font-size: 0.85rem; }
    .topic-stats strong { display: block; color: white; font-size: 1.2rem; }

    /* Mini Profil Kutusu (Sağ Taraf) */
    .mini-profile { background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 25px; text-align: center; }
    .mini-profile h2 { color: white; font-size: 1.2rem; margin-top: 0; }
    .mini-profile p { color: #8b949e; font-size: 0.9rem; margin-bottom: 20px; }
    .stat-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .stat-row:last-child { border: none; }
    .stat-row span { color: #8b949e; font-size: 0.95rem; }
    .stat-row strong { color: #f39c12; font-size: 1.1rem; }
    .create-btn { display: block; width: 100%; text-align: center; background: #f39c12; color: #111; padding: 12px 0; border-radius: 8px; font-weight: bold; text-decoration: none; margin-top: 20px; transition: 0.3s; }
    .create-btn:hover { background: #d68910; }

    /* Light Mode */
    body.light-mode .com-header, body.light-mode .topic-card, body.light-mode .mini-profile { background: #fff; border-color: #e2e8f0; }
    body.light-mode .search-bar input { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .search-btn { background: #e2e8f0; color: #475569; }
    body.light-mode .search-btn:hover { background: #3b82f6; color: white; }
    body.light-mode .topic-info h3, body.light-mode .mini-profile h2, body.light-mode .topic-stats strong { color: #0f172a; }
    body.light-mode .stat-row span, body.light-mode .topic-meta, body.light-mode .mini-profile p { color: #64748b; }
    body.light-mode .stat-row strong { color: #3b82f6; }
    body.light-mode .create-btn { background: #3b82f6; color: white; }
    body.light-mode .create-btn:hover { background: #2563eb; }
</style>

<div class="com-layout">
    <div class="com-main">
        <div class="com-header">
            <h2 style="color:white; margin:0; font-size:1.5rem;">Topluluk Konuları</h2>
            <form method="GET" action="community" class="search-bar">
                <input type="text" name="q" placeholder="Konu başlıklarında ara..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn">Ara</button>
            </form>
        </div>

        <div style="display:flex; flex-direction:column; gap:15px;">
            <?php if(empty($topics)): ?>
                <div style="padding: 40px; text-align:center; color:#8b949e; border:1px dashed #333; border-radius:12px;">
                    Aramanıza uygun konu bulunamadı.
                </div>
            <?php else: ?>
                <?php foreach($topics as $topic): ?>
                    <a href="topicdetail?id=<?php echo $topic['id']; ?>" class="topic-card">
                        <div class="topic-info">
                            <h3><?php echo htmlspecialchars($topic['title']); ?></h3>
                            <div class="topic-meta">
                                <span>@<?php echo htmlspecialchars($topic['author_username']); ?></span>
                                <span><?php echo date('d.m.Y H:i', strtotime($topic['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="topic-stats">
                            <strong><?php echo count($topic['replies']); ?></strong>
                            Yanıt
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="com-sidebar">
        <div class="mini-profile">
            <?php if(isset($_SESSION['is_logged_in'])): ?>
                <h2>Hoş Geldin, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                <p>Topluluktaki aktif istatistiklerin:</p>
                
                <div class="stat-row">
                    <span>Oluşturduğun Konular:</span>
                    <strong><?php echo $user_created_count; ?></strong>
                </div>
                <div class="stat-row">
                    <span>Katıldığın Konular:</span>
                    <strong><?php echo $user_replied_count; ?></strong>
                </div>

                <a href="createtopic" class="create-btn">Yeni Konu Aç</a>
            <?php else: ?>
                <h2>Hoş Geldin!</h2>
                <p>Soru sormak, cevap yazmak ve tartışmalara katılmak için giriş yapmalısın.</p>
                <a href="home" class="create-btn" style="background:#333; color:white;">Giriş Yap</a>
            <?php endif; ?>
        </div>
    </div>
</div>