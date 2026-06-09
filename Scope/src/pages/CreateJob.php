<?php
if (!isset($_SESSION['is_logged_in'])) {
    echo "<script>alert('İlan açmak için giriş yapmalısınız.'); window.location.href='freelancehub';</script>";
    exit;
}
?>
<style>
    .create-wrapper { max-width: 800px; width: 100%; margin: 40px auto; background: #1a1a1e; border: 1px solid #333; border-radius: 12px; padding: 40px; text-align: left; }
    .create-wrapper h1 { color: white; margin-top: 0; margin-bottom: 30px; font-size: 1.8rem; border-bottom: 1px solid #333; padding-bottom: 15px; }
    .form-group { margin-bottom: 25px; display: flex; flex-direction: column; gap: 8px; }
    .form-group label { color: #8b949e; font-weight: bold; font-size: 0.9rem; }
    .form-group input[type="text"], .form-group input[type="number"], .form-group textarea { background: #0d0d0f; border: 1px solid #333; color: white; padding: 15px; border-radius: 8px; outline: none; font-size: 1rem; width: 100%; box-sizing: border-box; }
    .form-group input:focus, .form-group textarea:focus { border-color: #f39c12; }
    .form-group textarea { min-height: 200px; resize: vertical; }
    .submit-btn { background: #f39c12; color: #111; border: none; padding: 15px 30px; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: 0.3s; width: 100%; }
    .submit-btn:hover { background: #d68910; }

    body.light-mode .create-wrapper { background: #fff; border-color: #e2e8f0; }
    body.light-mode .create-wrapper h1 { color: #0f172a; border-color: #e2e8f0; }
    body.light-mode .form-group label { color: #64748b; }
    body.light-mode .form-group input, body.light-mode .form-group textarea { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .submit-btn { background: #3b82f6; color: white; }
</style>

<div class="create-wrapper">
    <h1>Yeni Freelance İlanı Oluştur</h1>
    <form action="index.php" method="POST">
        <input type="hidden" name="action" value="create_job">
        <div class="form-group">
            <label>Ne yapılmasını istiyorsunuz? (Başlık)</label>
            <input type="text" name="title" required placeholder="Örn: E-ticaret sitem için 3D logo tasarımı">
        </div>
        <div class="form-group">
            <label>Detaylar</label>
            <textarea name="description" required placeholder="İşin detaylarını, beklentilerinizi ve teslim süresini yazın..."></textarea>
        </div>
        <div class="form-group">
            <label>Bütçe ($)</label>
            <input type="number" name="budget" required min="1" placeholder="100">
        </div>
        <button type="submit" class="submit-btn">İlanı Yayınla</button>
    </form>
</div>