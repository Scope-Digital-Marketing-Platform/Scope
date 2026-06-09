<?php
$freelance_db = __DIR__ . '/../../backend/database/freelance_data.json';
$jobs = file_exists($freelance_db) ? json_decode(file_get_contents($freelance_db), true) ?: [] : [];

// Yeniden eskiye sırala
usort($jobs, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });

$my_postings = 0;
$my_active_jobs = 0;

if (isset($_SESSION['is_logged_in'])) {
    foreach($jobs as $j) {
        if ($j['author_id'] === $_SESSION['user_id']) $my_postings++;
        if ($j['status'] === 'in_progress' && isset($j['accepted_bid']) && $j['accepted_bid']['user_id'] === $_SESSION['user_id']) {
            $my_active_jobs++;
        }
    }
}
?>
<style>
    body .page-content { padding: 0 !important; margin: 0 !important; }
    .com-layout { display: flex; gap: 30px; max-width: 1200px; width: 100%; margin: 40px auto; padding: 0 20px; align-items: flex-start; text-align: left; }
    .com-main { flex: 3; display: flex; flex-direction: column; gap: 20px; }
    .com-sidebar { flex: 1; min-width: 280px; position: sticky; top: 90px; }

    .com-header { display: flex; justify-content: space-between; align-items: center; background: #1a1a1e; padding: 20px; border-radius: 12px; border: 1px solid #333; }
    
    .job-card { background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; text-decoration: none; transition: all 0.3s; position: relative;}
    .job-card:hover { transform: translateX(5px); border-color: #f39c12; background: rgba(243, 156, 18, 0.03); }
    .job-info h3 { color: white; font-size: 1.2rem; margin: 0 0 10px 0; }
    .job-meta { color: #8b949e; font-size: 0.85rem; display: flex; gap: 15px; }
    
    .job-price { font-size: 1.4rem; font-weight: bold; color: #2ecc71; text-align: right;}
    .job-status { font-size: 0.8rem; padding: 4px 8px; border-radius: 4px; display: inline-block; margin-top: 5px; font-weight: bold;}
    .status-open { background: rgba(243, 156, 18, 0.2); color: #f39c12; }
    .status-progress { background: rgba(52, 152, 219, 0.2); color: #3498db; }
    .status-completed { background: rgba(46, 204, 113, 0.2); color: #2ecc71; }

    .mini-profile { background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 25px; text-align: center; }
    .mini-profile h2 { color: white; font-size: 1.2rem; margin-top: 0; }
    .stat-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .stat-row:last-child { border: none; }
    .stat-row span { color: #8b949e; font-size: 0.95rem; }
    .stat-row strong { color: #f39c12; font-size: 1.1rem; }
    .create-btn { display: block; width: 100%; text-align: center; background: #f39c12; color: #111; padding: 12px 0; border-radius: 8px; font-weight: bold; text-decoration: none; margin-top: 20px; transition: 0.3s; }
    .create-btn:hover { background: #d68910; }

    /* Light Mode */
    body.light-mode .com-header, body.light-mode .job-card, body.light-mode .mini-profile { background: #fff; border-color: #e2e8f0; }
    body.light-mode .job-info h3, body.light-mode .mini-profile h2 { color: #0f172a; }
    body.light-mode .stat-row span, body.light-mode .job-meta { color: #64748b; }
    body.light-mode .stat-row strong { color: #3b82f6; }
    body.light-mode .create-btn { background: #3b82f6; color: white; }
    body.light-mode .create-btn:hover { background: #2563eb; }
</style>

<div class="com-layout">
    <div class="com-main">
        <div class="com-header">
            <h2 style="color:inherit; margin:0;">Freelance İlanları</h2>
            <p style="color:#8b949e; margin:0;">Yeteneklerini paraya dönüştür veya projeni uzmanlara yaptır.</p>
        </div>

        <div style="display:flex; flex-direction:column; gap:15px;">
            <?php if(empty($jobs)): ?>
                <div style="padding: 40px; text-align:center; color:#8b949e; border:1px dashed #333; border-radius:12px;">
                    Henüz hiç ilan oluşturulmamış.
                </div>
            <?php else: ?>
                <?php foreach($jobs as $job): ?>
                    <a href="jobdetail?id=<?php echo $job['id']; ?>" class="job-card">
                        <div class="job-info">
                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                            <div class="job-meta">
                                <span>Alıcı: @<?php echo htmlspecialchars($job['author_username']); ?></span>
                                <span><?php echo count($job['bids']); ?> Teklif</span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div class="job-price"><?php echo htmlspecialchars($job['budget']); ?> $</div>
                            <?php if($job['status'] === 'open'): ?>
                                <span class="job-status status-open">Açık İlan</span>
                            <?php elseif($job['status'] === 'in_progress'): ?>
                                <span class="job-status status-progress">Yapım Aşamasında</span>
                            <?php else: ?>
                                <span class="job-status status-completed">Tamamlandı</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="com-sidebar">
        <div class="mini-profile">
            <?php if(isset($_SESSION['is_logged_in'])): ?>
                <h2>Freelance Özeti</h2>
                <div class="stat-row">
                    <span>Açtığım İlanlar:</span>
                    <strong><?php echo $my_postings; ?></strong>
                </div>
                <div class="stat-row">
                    <span>Aldığım İşler:</span>
                    <strong><?php echo $my_active_jobs; ?></strong>
                </div>
                <a href="createjob" class="create-btn">Yeni İlan Oluştur</a>
            <?php else: ?>
                <h2>Hoş Geldin!</h2>
                <p style="color:#8b949e; font-size:14px;">İlan vermek veya iş almak için giriş yapmalısın.</p>
                <a href="home" class="create-btn" style="background:#333; color:white;">Giriş Yap</a>
            <?php endif; ?>
        </div>
    </div>
</div>