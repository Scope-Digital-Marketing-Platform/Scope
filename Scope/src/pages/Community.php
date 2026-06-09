<?php
$topic_file_path = __DIR__ . '/../../backend/database/topics_data.json';
$users_db_path = __DIR__ . '/../../backend/database/users_data.json';

$topics = [];
if (file_exists($topic_file_path)) {
    $topics = json_decode(file_get_contents($topic_file_path), true) ?: [];
}
usort($topics, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });

$search_query = isset($_GET['q']) ? trim(strtolower($_GET['q'])) : '';
$filter_mode = isset($_GET['filter']) ? $_GET['filter'] : '';

if (!empty($search_query)) {
    $topics = array_filter($topics, function($t) use ($search_query) { return strpos(strtolower($t['title']), $search_query) !== false; });
}
if ($filter_mode === 'my_topics' && isset($_SESSION['user_id'])) {
    $topics = array_filter($topics, function($t) { return $t['author_id'] === $_SESSION['user_id']; });
}

$user_created_count = 0;
$user_replied_count = 0;

if (isset($_SESSION['is_logged_in'])) {
    // Sayacı sadece gerçekten var olan (silinmemiş) konular üzerinden hesapla
    foreach ($topics as $t) {
        if ($t['author_id'] === $_SESSION['user_id']) {
            $user_created_count++;
        }
        // Kullanıcının bu konuya yanıt verip vermediğini kontrol et
        $has_replied = false;
        if (isset($t['replies'])) {
            foreach ($t['replies'] as $reply) {
                if ($reply['user_id'] === $_SESSION['user_id']) {
                    $has_replied = true;
                    break;
                }
            }
        }
        if ($has_replied) {
            $user_replied_count++;
        }
    }
}
?>

<div class="home-wrapper" style="width: 100%; position: relative; overflow: hidden; min-height: 100vh;">
    <div class="floating-particles">
        <div class="particle p-1">$</div> <div class="particle p-2">✕</div> <div class="particle p-3">◯</div> <div class="particle p-4">&lt;/&gt;</div> <div class="particle p-5">✦</div>
        <div class="particle p-11">{}</div> <div class="particle p-12">◯</div> <div class="particle p-13">$</div> <div class="particle p-14">✕</div> <div class="particle p-15">&lt;/&gt;</div>
        <div class="particle p-6">$</div> <div class="particle p-7">✕</div> <div class="particle p-8">◯</div> <div class="particle p-9">{}</div> <div class="particle p-10">✦</div>
        <div class="particle p-16">✕</div> <div class="particle p-17">&lt;/&gt;</div> <div class="particle p-18">◯</div> <div class="particle p-19">$</div> <div class="particle p-20">✦</div>
    </div>

    <style>
        body .page-content { padding: 0 !important; margin: 0 !important; }
        
        /* Animasyon CSS Konumları - Burası kırpılmamalı! */
        .floating-particles { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
        .particle { position: absolute; color: rgba(255, 255, 255, 0.08); font-size: 28px; font-weight: 900; animation: fall linear infinite; }
        .particle:nth-child(odd) { color: rgba(243, 156, 18, 0.08); }
        @keyframes fall { 0% { transform: translateY(-50px) rotate(0deg); opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { transform: translateY(120vh) rotate(360deg); opacity: 0; } }
        .p-1 { left: 3%; animation-duration: 15s; animation-delay: -5s; font-size: 32px;}
        .p-2 { left: 9%; animation-duration: 22s; animation-delay: -14s; }
        .p-3 { left: 14%; animation-duration: 18s; animation-delay: -2s; font-size: 24px;}
        .p-4 { left: 6%; animation-duration: 25s; animation-delay: -19s; font-size: 30px;}
        .p-5 { left: 11%; animation-duration: 19s; animation-delay: -8s; }
        .p-11 { left: 2%; animation-duration: 16s; animation-delay: -11s; font-size: 28px;}
        .p-12 { left: 13%; animation-duration: 24s; animation-delay: -4s; font-size: 22px;}
        .p-13 { left: 8%; animation-duration: 20s; animation-delay: -21s; font-size: 35px;}
        .p-14 { left: 5%; animation-duration: 18s; animation-delay: -1s; font-size: 26px;}
        .p-15 { left: 12%; animation-duration: 23s; animation-delay: -16s; font-size: 31px;}
        .p-6 { left: 96%; animation-duration: 21s; animation-delay: -11s; font-size: 35px;}
        .p-7 { left: 88%; animation-duration: 16s; animation-delay: -3s; }
        .p-8 { left: 92%; animation-duration: 24s; animation-delay: -17s; font-size: 26px;}
        .p-9 { left: 86%; animation-duration: 20s; animation-delay: -9s; font-size: 28px;}
        .p-10 { left: 98%; animation-duration: 17s; animation-delay: -15s; font-size: 22px;}
        .p-16 { left: 89%; animation-duration: 22s; animation-delay: -6s; font-size: 32px;}
        .p-17 { left: 94%; animation-duration: 19s; animation-delay: -20s; font-size: 29px;}
        .p-18 { left: 97%; animation-duration: 25s; animation-delay: -1s; font-size: 24px;}
        .p-19 { left: 85%; animation-duration: 15s; animation-delay: -13s; font-size: 34px;}
        .p-20 { left: 91%; animation-duration: 23s; animation-delay: -8s; font-size: 27px;}

        .com-layout { display: flex; gap: 30px; max-width: 1200px; width: 100%; margin: 40px auto; padding: 0 20px; align-items: flex-start; text-align: left; position: relative; z-index: 10;}
        .com-main { flex: 3; display: flex; flex-direction: column; gap: 20px; }
        .com-sidebar { flex: 1; min-width: 280px; position: sticky; top: 90px; }

        .com-header { display: flex; justify-content: space-between; align-items: center; background: rgba(26, 26, 30, 0.8); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); }
        .search-bar { display: flex; gap: 10px; width: 100%; max-width: 400px; }
        .search-bar input { flex:1; background: #0d0d0f; border: 1px solid #333; color: white; padding: 12px 15px; border-radius: 8px; outline: none; }
        .search-btn { background: #333; color: white; border: none; padding: 0 20px; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .search-btn:hover { background: #f39c12; color: #111; }

        .topic-card { background: rgba(26, 26, 30, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; text-decoration: none; transition: all 0.3s; }
        .topic-card:hover { transform: translateX(5px); border-color: #f39c12; background: rgba(243, 156, 18, 0.1); }
        .topic-info h3 { color: white; font-size: 1.2rem; margin: 0 0 10px 0; }
        .topic-meta { color: #8b949e; font-size: 0.85rem; display: flex; gap: 15px; }
        .topic-stats { text-align: center; color: #8b949e; font-size: 0.85rem; }
        .topic-stats strong { display: block; color: white; font-size: 1.2rem; }

        .mini-profile { background: rgba(26, 26, 30, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 25px; text-align: center; }
        .mini-profile h2 { color: white; font-size: 1.2rem; margin-top: 0; }
        .mini-profile p { color: #8b949e; font-size: 0.9rem; margin-bottom: 20px; }
        .stat-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .stat-row:last-child { border: none; }
        .stat-row span { color: #8b949e; font-size: 0.95rem; }
        .stat-row strong { color: #f39c12; font-size: 1.1rem; }
        .create-btn { display: block; width: 100%; text-align: center; background: #f39c12; color: #111; padding: 12px 0; border-radius: 8px; font-weight: bold; text-decoration: none; margin-top: 20px; transition: 0.3s; }
        .create-btn:hover { background: #d68910; }
        .my-topics-btn { display: block; width: 100%; text-align: center; background: transparent; color: #8b949e; padding: 10px 0; border: 1px solid #333; border-radius: 8px; font-weight: bold; text-decoration: none; margin-top: 10px; transition: 0.3s; }
        .my-topics-btn:hover { border-color: #f39c12; color: white; background: rgba(243, 156, 18, 0.1); }

        body.light-mode .com-header, body.light-mode .topic-card, body.light-mode .mini-profile { background: rgba(255, 255, 255, 0.9); border-color: rgba(0,0,0,0.05); }
        body.light-mode .search-bar input { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        body.light-mode .search-btn { background: #e2e8f0; color: #475569; }
        body.light-mode .search-btn:hover { background: #3b82f6; color: white; }
        body.light-mode .topic-info h3, body.light-mode .mini-profile h2, body.light-mode .topic-stats strong { color: #0f172a; }
        body.light-mode .stat-row span, body.light-mode .topic-meta, body.light-mode .mini-profile p { color: #64748b; }
        body.light-mode .stat-row strong { color: #3b82f6; }
        body.light-mode .create-btn { background: #3b82f6; color: white; }
        body.light-mode .create-btn:hover { background: #2563eb; }
        body.light-mode .my-topics-btn { border-color: #cbd5e1; color: #64748b; }
        body.light-mode .my-topics-btn:hover { border-color: #3b82f6; color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
        body.light-mode .particle { color: rgba(59, 130, 246, 0.18); }
        body.light-mode .particle:nth-child(odd) { color: rgba(15, 23, 42, 0.12); }
    </style>

    <div class="com-layout">
        <div class="com-main">
            <div class="com-header">
                <h2 style="color:inherit; margin:0; font-size:1.5rem;">
                    <?php echo $filter_mode === 'my_topics' ? 'Benim Konularım' : 'Topluluk Konuları'; ?>
                </h2>
                <form method="GET" action="community" class="search-bar">
                    <input type="text" name="q" placeholder="Konu başlıklarında ara..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn">Ara</button>
                </form>
            </div>

            <div style="display:flex; flex-direction:column; gap:15px;">
                <?php if(empty($topics)): ?>
                    <div style="padding: 40px; text-align:center; color:#8b949e; border:1px dashed rgba(255,255,255,0.1); border-radius:12px; backdrop-filter: blur(10px);">
                        Konu bulunamadı.
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
                        <span>Oluşturulan Konular:</span>
                        <strong><?php echo $user_created_count; ?></strong>
                    </div>
                    <div class="stat-row">
                        <span>Katıldığın Konular:</span>
                        <strong><?php echo $user_replied_count; ?></strong>
                    </div>

                    <a href="createtopic" class="create-btn">Yeni Konu Aç</a>
                    <a href="community?filter=my_topics" class="my-topics-btn">Benim Konularım</a>
                    <?php if($filter_mode === 'my_topics'): ?>
                        <a href="community" style="color:#8b949e; font-size:12px; margin-top:10px; display:block;">Filtreyi Temizle</a>
                    <?php endif; ?>
                <?php else: ?>
                    <h2>Hoş Geldin!</h2>
                    <p>Soru sormak, cevap yazmak ve tartışmalara katılmak için giriş yapmalısın.</p>
                    <a href="home" class="create-btn" style="background:#333; color:white;">Giriş Yap</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>