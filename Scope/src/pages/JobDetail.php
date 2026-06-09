<?php
$job_id = isset($_GET['id']) ? $_GET['id'] : '';
$freelance_db = __DIR__ . '/../../backend/database/freelance_data.json';
$jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : [];

$job = null;
foreach ($jobs as $j) { if ($j['id'] === $job_id) { $job = $j; break; } }
if (!$job) { echo "<div style='padding:100px; color:white;'><h2>İlan bulunamadı.</h2></div>"; exit; }

$is_author = isset($_SESSION['user_id']) && $job['author_id'] === $_SESSION['user_id'];
$is_freelancer = isset($_SESSION['user_id']) && isset($job['accepted_bid']) && $job['accepted_bid']['user_id'] === $_SESSION['user_id'];

$lowest_bid = null;
if (!empty($job['bids'])) {
    $lowest_bid = $job['bids'][0]['amount'];
    foreach($job['bids'] as $b) { if($b['amount'] < $lowest_bid) $lowest_bid = $b['amount']; }
}
?>
<style>
    .jd-wrapper { max-width: 900px; width: 100%; margin: 40px auto; text-align: left; }
    .jd-main { background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 30px; margin-bottom: 30px; }
    .jd-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;}
    .jd-title { color: white; font-size: 2rem; margin: 0; }
    .jd-price { font-size: 2rem; font-weight: bold; color: #2ecc71; margin: 0;}
    .jd-meta { color: #8b949e; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; margin-bottom: 20px; }
    .jd-content { color: #c9d1d9; font-size: 1.1rem; line-height: 1.6; white-space: pre-wrap; }
    
    .section-title { color: white; font-size: 1.4rem; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    
    .bid-card { background: #16161a; border: 1px solid #2a2a30; border-radius: 8px; padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;}
    .bid-info strong { color: #fff; font-size: 1.1rem; display: block; margin-bottom: 5px; }
    .bid-info a { color: #f39c12; text-decoration: none; font-size: 0.9rem; }
    .bid-info p { color: #8b949e; margin: 10px 0 0 0; font-style: italic; }
    .bid-action { text-align: right; }
    .bid-amount { color: #2ecc71; font-size: 1.5rem; font-weight: bold; margin-bottom: 10px;}
    
    .btn { padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; border: none; text-decoration: none; display: inline-block; transition: 0.3s; font-size: 0.9rem;}
    .btn-primary { background: #f39c12; color: #111; }
    .btn-danger { background: #ff4a4a; color: white; }
    .btn-success { background: #2ecc71; color: white; }
    .btn:hover { opacity: 0.9; }

    .action-box { background: #1a1a1e; border: 1px dashed #f39c12; border-radius: 12px; padding: 25px; margin-top: 30px; }
    .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;}
    .form-group input, .form-group textarea { background: #0d0d0f; border: 1px solid #333; color: white; padding: 12px; border-radius: 8px; outline: none; }
    
    body.light-mode .jd-main, body.light-mode .bid-card, body.light-mode .action-box { background: #fff; border-color: #e2e8f0; }
    body.light-mode .jd-title, body.light-mode .section-title, body.light-mode .bid-info strong { color: #0f172a; border-color: #e2e8f0; }
    body.light-mode .jd-content, body.light-mode .jd-meta, body.light-mode .bid-info p { color: #475569; }
    body.light-mode .btn-primary { background: #3b82f6; color: white; }
    body.light-mode .action-box { border-color: #3b82f6; }
    body.light-mode .form-group input, body.light-mode .form-group textarea { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
</style>

<div class="jd-wrapper">
    <div class="jd-main">
        <div class="jd-header">
            <h1 class="jd-title"><?php echo htmlspecialchars($job['title']); ?></h1>
            <div style="text-align:right;">
                <div class="jd-price"><?php echo htmlspecialchars($job['budget']); ?> $</div>
                <?php if($is_author && $job['status'] === 'open'): ?>
                    <div style="margin-top: 10px; display:flex; gap:10px; justify-content:flex-end;">
                        <a href="editjob?id=<?php echo urlencode($job['id']); ?>" class="btn btn-primary" style="padding: 6px 12px;">Düzenle</a>
                        <form method="POST" action="index.php" style="margin:0;">
                            <input type="hidden" name="action" value="delete_job">
                            <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 6px 12px;" onclick="return confirm('Bu ilanı tamamen silmek istiyor musunuz?');">Kaldır</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="jd-meta">
            Oluşturan: <a href="profile?user=<?php echo htmlspecialchars($job['author_username']); ?>" style="color:#f39c12; text-decoration:none;">@<?php echo htmlspecialchars($job['author_username']); ?></a> | 
            Durum: <strong><?php echo strtoupper($job['status']); ?></strong>
        </div>
        <div class="jd-content"><?php echo nl2br(htmlspecialchars($job['description'])); ?></div>
    </div>

    <?php if($job['status'] === 'open'): ?>
        <h3 class="section-title">Gelen Teklifler <?php echo $lowest_bid ? "(En düşük: {$lowest_bid} $)" : ""; ?></h3>
        <?php if(empty($job['bids'])): ?>
            <p style="color:#8b949e;">Henüz teklif verilmemiş.</p>
        <?php else: ?>
            <?php foreach($job['bids'] as $bid): ?>
                <div class="bid-card">
                    <div class="bid-info">
                        <strong>@<?php echo htmlspecialchars($bid['username']); ?></strong>
                        <a href="profile?user=<?php echo htmlspecialchars($bid['username']); ?>">Profili İncele</a>
                        <p>"<?php echo htmlspecialchars($bid['message']); ?>"</p>
                    </div>
                    <div class="bid-action">
                        <div class="bid-amount"><?php echo htmlspecialchars($bid['amount']); ?> $</div>
                        <?php if($is_author): ?>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="action" value="accept_bid">
                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                <input type="hidden" name="bidder_id" value="<?php echo $bid['user_id']; ?>">
                                <button type="submit" class="btn btn-success" onclick="return confirm('Bu teklifi kabul etmek istediğinize emin misiniz? İşe hemen başlanacak.');">Teklifi Kabul Et</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if(!$is_author && isset($_SESSION['user_id'])): ?>
            <div class="action-box">
                <h3 style="color:inherit; margin-top:0;">Bu işe talip ol!</h3>
                <form action="index.php" method="POST">
                    <input type="hidden" name="action" value="place_bid">
                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                    <div class="form-group">
                        <label>Teklifiniz ($)</label>
                        <input type="number" name="amount" required placeholder="İşi kaç dolara yaparsınız?">
                    </div>
                    <div class="form-group">
                        <label>Mesajınız</label>
                        <textarea name="message" required placeholder="Neden sizi seçmeli? Detaylandırın..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Teklif Gönder</button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($job['status'] === 'in_progress'): ?>
        <div class="action-box" style="border-color:#3498db;">
            <h3 style="color:#3498db; margin-top:0;">İş Yapım Aşamasında</h3>
            <p>İşi Yapan: <strong>@<?php echo htmlspecialchars($job['accepted_bid']['username']); ?></strong></p>
            <?php if($job['delivery']): ?>
                <div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:8px; margin-top:15px;">
                    <strong style="color:white;">Teslimat Mesajı:</strong>
                    <p style="color:#c9d1d9;"><?php echo nl2br(htmlspecialchars($job['delivery']['message'])); ?></p>
                    <?php if($job['delivery']['file']): ?>
                        <a href="<?php echo htmlspecialchars($job['delivery']['file']); ?>" download class="btn btn-primary" style="margin-bottom:15px;">Dosyayı İndir</a>
                    <?php endif; ?>
                    <?php if($is_author): ?>
                        <hr style="border-color:#333; margin:20px 0;">
                        <form action="index.php" method="POST">
                            <input type="hidden" name="action" value="approve_job">
                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                            <button type="submit" class="btn btn-success" style="width:100%;">İşi Onayla & Tamamla</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php if($is_freelancer): ?>
                    <form action="index.php" method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                        <input type="hidden" name="action" value="deliver_job">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <div class="form-group"><label>Teslimat Notu</label><textarea name="message" required placeholder="İşi bitirdiğinizi bildirin..."></textarea></div>
                        <div class="form-group"><label>Proje Dosyası</label><input type="file" name="delivery_file" required></div>
                        <button type="submit" class="btn btn-success">İşi Teslim Et</button>
                    </form>
                <?php else: ?>
                    <p style="color:#8b949e; font-style:italic;">Freelancer şu anda bu proje üzerinde çalışıyor. Teslimat bekleniyor...</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($job['status'] === 'completed'): ?>
        <div class="action-box" style="border-color:#2ecc71; text-align:center;">
            <svg width="60" height="60" fill="none" stroke="#2ecc71" viewBox="0 0 24 24" style="margin-bottom:15px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h2 style="color:#2ecc71; margin-top:0;">Bu İş Başarıyla Tamamlandı!</h2>
            <p style="color:#8b949e;">İşi Yapan: <strong>@<?php echo htmlspecialchars($job['accepted_bid']['username']); ?></strong></p>
            <?php if($job['delivery'] && $job['delivery']['file'] && ($is_author || $is_freelancer)): ?>
                <a href="<?php echo htmlspecialchars($job['delivery']['file']); ?>" download class="btn btn-primary" style="margin-top:10px;">Teslim Edilen Dosyayı İndir</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>