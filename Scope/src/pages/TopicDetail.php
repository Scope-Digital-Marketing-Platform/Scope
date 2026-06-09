<?php
$topic_id = isset($_GET['id']) ? $_GET['id'] : '';
$topic_db = __DIR__ . '/../../backend/database/topics_data.json';
$topics = file_exists($topic_db) ? json_decode(file_get_contents($topic_db), true) ?: [] : [];

$current_topic = null;
foreach ($topics as $t) {
    if ($t['id'] === $topic_id) {
        $current_topic = $t;
        break;
    }
}

if (!$current_topic) {
    echo "<div style='padding:100px; color:white;'><h2>Konu bulunamadı veya silinmiş.</h2></div>";
    exit;
}
?>

<style>
    .td-wrapper { max-width: 900px; width: 100%; margin: 40px auto; text-align: left; }
    
    /* Konu Ana Kutusu */
    .td-main { background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 30px; margin-bottom: 30px; }
    .td-title { color: white; font-size: 2rem; margin-top: 0; margin-bottom: 10px; }
    .td-meta { color: #8b949e; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; margin-bottom: 20px; }
    .td-meta strong { color: #f39c12; }
    .td-content { color: #c9d1d9; font-size: 1.1rem; line-height: 1.6; white-space: pre-wrap; }
    
    /* Konu Görselleri */
    .td-gallery { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 25px; }
    .td-gallery img { max-width: 100%; max-height: 400px; border-radius: 8px; border: 1px solid #333; }

    /* Yorumlar */
    .td-replies-header { color: white; font-size: 1.4rem; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .reply-card { background: #16161a; border: 1px solid #2a2a30; border-radius: 8px; padding: 20px; margin-bottom: 15px; }
    .reply-meta { display: flex; justify-content: space-between; margin-bottom: 10px; color: #8b949e; font-size: 0.85rem; }
    .reply-meta strong { color: #fff; font-size: 1rem; }
    .reply-text { color: #c9d1d9; line-height: 1.5; white-space: pre-wrap; }

    /* Yanıt Formu */
    .reply-form-box { background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 25px; margin-top: 40px; }
    .reply-form-box textarea { width: 100%; background: #0d0d0f; border: 1px solid #333; color: white; padding: 15px; border-radius: 8px; resize: vertical; min-height: 120px; outline: none; box-sizing: border-box; font-size: 1rem; margin-bottom: 15px; }
    .reply-form-box textarea:focus { border-color: #f39c12; }
    .reply-btn { background: #f39c12; color: #111; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
    .reply-btn:hover { background: #d68910; }

    /* Light Mode */
    body.light-mode .td-main, body.light-mode .reply-form-box { background: #fff; border-color: #e2e8f0; }
    body.light-mode .td-title, body.light-mode .td-replies-header, body.light-mode .reply-meta strong { color: #0f172a; border-color: #e2e8f0; }
    body.light-mode .td-meta, body.light-mode .td-content, body.light-mode .reply-text { color: #475569; }
    body.light-mode .td-meta strong { color: #3b82f6; }
    body.light-mode .reply-card { background: #f8fafc; border-color: #e2e8f0; }
    body.light-mode .reply-form-box textarea { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .reply-form-box textarea:focus { border-color: #3b82f6; }
    body.light-mode .reply-btn { background: #3b82f6; color: white; }
    body.light-mode .reply-btn:hover { background: #2563eb; }
</style>

<div class="td-wrapper">
    
    <div class="td-main">
        <h1 class="td-title"><?php echo htmlspecialchars($current_topic['title']); ?></h1>
        <div class="td-meta">
            Oluşturan: <strong>@<?php echo htmlspecialchars($current_topic['author_username']); ?></strong> | 
            Tarih: <?php echo date('d.m.Y H:i', strtotime($current_topic['created_at'])); ?>
        </div>
        <div class="td-content"><?php echo htmlspecialchars($current_topic['content']); ?></div>
        
        <?php if(!empty($current_topic['images'])): ?>
            <div class="td-gallery">
                <?php foreach($current_topic['images'] as $img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Topic Image">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <h3 class="td-replies-header">Yanıtlar (<?php echo count($current_topic['replies']); ?>)</h3>
    
    <?php if(empty($current_topic['replies'])): ?>
        <p style="color:#8b949e; font-style:italic;">Henüz kimse yanıt vermemiş. İlk yanıtı sen yaz!</p>
    <?php else: ?>
        <?php foreach($current_topic['replies'] as $reply): ?>
            <div class="reply-card">
                <div class="reply-meta">
                    <strong>@<?php echo htmlspecialchars($reply['username']); ?></strong>
                    <span><?php echo date('d.m.Y H:i', strtotime($reply['date'])); ?></span>
                </div>
                <div class="reply-text"><?php echo htmlspecialchars($reply['message']); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="reply-form-box">
        <?php if(isset($_SESSION['is_logged_in'])): ?>
            <h3 style="color:inherit; margin-top:0;">Yanıt Yaz</h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="add_reply">
                <input type="hidden" name="topic_id" value="<?php echo htmlspecialchars($current_topic['id']); ?>">
                <textarea name="message" required placeholder="Bu konuya düşünceni ekle..."></textarea>
                <button type="submit" class="reply-btn">Gönder</button>
            </form>
        <?php else: ?>
            <p style="color:#8b949e;">Yanıt yazabilmek için <a href="home" style="color:#f39c12;">giriş yapmalısınız</a>.</p>
        <?php endif; ?>
    </div>

</div>