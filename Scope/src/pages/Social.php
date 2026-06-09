<?php
if (!isset($_SESSION['is_logged_in'])) { echo "<script>alert('Mesajlaşmak için giriş yapmalısınız.'); window.location.href='home';</script>"; exit; }

$social_db_path = __DIR__ . '/../../backend/database/social_data.json';
$users_db_path = __DIR__ . '/../../backend/database/users_data.json';

$all_messages = file_exists($social_db_path) ? json_decode(file_get_contents($social_db_path), true) ?: [] : [];
$users_db = file_exists($users_db_path) ? json_decode(file_get_contents($users_db_path), true) ?: [] : [];

$my_id = $_SESSION['user_id'];
$active_chat_username = isset($_GET['user']) ? trim($_GET['user']) : null;

// DÜZ MANTIKLA GRUPLAMA VE HATA KONTROLÜ
$conversations = [];
$active_messages = [];
$db_updated = false;

foreach ($all_messages as &$msg) {
    // HATA ÖNLEYİCİ: Eski (gruplu) formattan kalan veya bozuk verileri atla
    if (!isset($msg['sender_id']) || !isset($msg['receiver_id'])) continue;

    if ($msg['sender_id'] === $my_id || $msg['receiver_id'] === $my_id) {
        $other_username = ($msg['sender_id'] === $my_id) ? $msg['receiver_username'] : $msg['sender_username'];
        
        // Son mesaj tarihini bul
        if (!isset($conversations[$other_username]) || $msg['date'] > $conversations[$other_username]) {
            $conversations[$other_username] = $msg['date'];
        }
    }

    if ($active_chat_username) {
        $is_relevant = ($msg['sender_id'] === $my_id && $msg['receiver_username'] === $active_chat_username) ||
                       ($msg['receiver_id'] === $my_id && $msg['sender_username'] === $active_chat_username);
        
        if ($is_relevant) {
            $active_messages[] = $msg;
            if ($msg['receiver_id'] === $my_id && !$msg['read']) {
                $msg['read'] = true;
                $db_updated = true;
            }
        }
    }
}
if ($db_updated) file_put_contents($social_db_path, json_encode($all_messages, JSON_PRETTY_PRINT));
arsort($conversations); // Yeniden eskiye sırala

$search_query = isset($_GET['search']) ? trim(strtolower($_GET['search'])) : '';
$search_results = [];
if (!empty($search_query)) {
    foreach ($users_db as $u) {
        if ($u['id'] !== $my_id && strpos(strtolower($u['username']), $search_query) !== false) {
            $search_results[] = $u['username'];
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
    
    .floating-particles { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
    .particle { position: absolute; color: rgba(255, 255, 255, 0.08); font-size: 28px; font-weight: 900; animation: fall linear infinite; }
    .particle:nth-child(odd) { color: rgba(243, 156, 18, 0.08); }
    @keyframes fall { 0% { transform: translateY(-50px) rotate(0deg); opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { transform: translateY(120vh) rotate(360deg); opacity: 0; } }
    
    /* Animasyon Koordinatları */
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
    
    /* YUMUŞAK KENARLI GENİŞ SOHBET KUTUSU */
    .chat-box-container { display: flex; width: 95%; max-width: 1400px; height: 82vh; margin: 40px auto; background: rgba(22, 22, 26, 0.85); backdrop-filter: blur(12px); border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.05); overflow: hidden; position: relative; z-index: 10; }
    
    /* Sol Taraf - Kişi Listesi */
    .chat-sidebar { width: 320px; background: rgba(0,0,0,0.2); border-right: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; }
    .chat-search-box { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .chat-search-box input { width: 100%; background: #0d0d0f; border: 1px solid #333; color: white; padding: 12px 15px; border-radius: 8px; outline: none; box-sizing: border-box; transition: 0.3s; }
    .chat-search-box input:focus { border-color: #f39c12; }
    
    .contact-list { flex: 1; overflow-y: auto; }
    .contact-item { padding: 15px 20px; display: flex; align-items: center; gap: 15px; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.02); transition: 0.2s; }
    .contact-item:hover { background: rgba(255,255,255,0.05); }
    .contact-item.active { background: rgba(243, 156, 18, 0.1); border-left: 3px solid #f39c12; }
    .contact-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; }
    .contact-info { flex: 1; text-align: left;}
    .contact-name { color: white; font-weight: bold; font-size: 0.95rem; }
    
    /* Sağ Taraf - Mesaj Alanı */
    .chat-main { flex: 1; display: flex; flex-direction: column; position: relative; }
    
    .chat-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); color: white; font-size: 1.2rem; font-weight: bold; display: flex; align-items: center; gap: 15px;}
    
    .chat-messages { flex: 1; overflow-y: auto; padding: 30px; display: flex; flex-direction: column; gap: 15px; }
    .msg-bubble { max-width: 60%; padding: 12px 18px; border-radius: 12px; font-size: 0.95rem; line-height: 1.4; position: relative; text-align: left; word-wrap: break-word;}
    .msg-time { font-size: 0.7rem; opacity: 0.6; margin-top: 5px; text-align: right; }
    
    .msg-left { align-self: flex-start; background: #2a2a35; color: #fff; border-bottom-left-radius: 2px; }
    .msg-right { align-self: flex-end; background: #f39c12; color: #111; border-bottom-right-radius: 2px; }
    
    .msg-file-link { display: inline-flex; align-items: center; gap: 5px; color: inherit; text-decoration: underline; font-weight: bold; word-break: break-all;}

    .chat-input-area { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); background: rgba(0,0,0,0.1); }
    .chat-form { display: flex; gap: 10px; align-items: center; }
    .chat-form input[type="text"] { flex: 1; background: #0d0d0f; border: 1px solid #333; color: white; padding: 15px; border-radius: 8px; outline: none; transition: 0.3s; }
    .chat-form input[type="text"]:focus { border-color: #f39c12; }
    .chat-form input[type="file"] { display: none; }
    .file-btn { background: transparent; border: 1px dashed #555; padding: 12px 15px; border-radius: 8px; color: #8b949e; cursor: pointer; transition: 0.2s;}
    .file-btn:hover { border-color: #f39c12; color: #f39c12; }
    .send-btn { background: #f39c12; color: #111; border: none; padding: 0 25px; height: 50px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
    
    /* Light Mode */
    body.light-mode .chat-box-container { background: rgba(255, 255, 255, 0.95); border-color: rgba(0,0,0,0.1); box-shadow: 0 10px 40px rgba(0,0,0,0.1);}
    body.light-mode .chat-sidebar { background: rgba(248, 250, 252, 0.8); border-color: #e2e8f0; }
    body.light-mode .chat-header, body.light-mode .chat-input-area { border-color: #e2e8f0; }
    body.light-mode .chat-search-box input, body.light-mode .chat-form input[type="text"] { background: #ffffff; border-color: #cbd5e1; color: #1e293b; }
    body.light-mode .contact-name, body.light-mode .chat-header { color: #0f172a; }
    body.light-mode .contact-item { border-color: #e2e8f0; }
    body.light-mode .contact-item:hover { background: #f1f5f9; }
    body.light-mode .contact-item.active { background: rgba(59, 130, 246, 0.1); border-left-color: #3b82f6; }
    body.light-mode .msg-left { background: #e2e8f0; color: #1e293b; }
    body.light-mode .msg-right { background: #3b82f6; color: white; }
    body.light-mode .send-btn { background: #3b82f6; color: white; }
    body.light-mode .particle { color: rgba(59, 130, 246, 0.18); }
    body.light-mode .particle:nth-child(odd) { color: rgba(15, 23, 42, 0.12); }
</style>

<div class="chat-box-container">
    <div class="chat-sidebar">
        <form method="GET" action="social" class="chat-search-box">
            <input type="text" name="search" placeholder="Kullanıcı Ara (Enter)..." value="<?php echo htmlspecialchars($search_query); ?>">
        </form>
        
        <div class="contact-list">
            <?php if(!empty($search_results)): ?>
                <div style="padding: 10px 20px; font-size: 0.8rem; color:#8b949e; font-weight:bold; text-align:left;">Arama Sonuçları</div>
                <?php foreach($search_results as $u): ?>
                    <a href="social?user=<?php echo urlencode($u); ?>" class="contact-item">
                        <div class="contact-avatar" style="background: #555;"><?php echo strtoupper(substr($u, 0, 1)); ?></div>
                        <div class="contact-info"><div class="contact-name">@<?php echo htmlspecialchars($u); ?></div></div>
                    </a>
                <?php endforeach; ?>
                <hr style="border-color:rgba(255,255,255,0.05); margin:0;">
            <?php endif; ?>

            <div style="padding: 10px 20px; font-size: 0.8rem; color:#8b949e; font-weight:bold; text-align:left;">Sohbetler</div>
            <?php if(empty($conversations)): ?>
                <p style="padding:20px; color:#555; text-align:center; font-size:0.9rem;">Henüz hiç konuşmanız yok.</p>
            <?php else: ?>
                <?php foreach($conversations as $username => $last_date): ?>
                    <a href="social?user=<?php echo urlencode($username); ?>" class="contact-item <?php echo ($active_chat_username === $username) ? 'active' : ''; ?>">
                        <div class="contact-avatar" style="background: linear-gradient(135deg, #f39c12, #d35400);"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                        <div class="contact-info">
                            <div class="contact-name">@<?php echo htmlspecialchars($username); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="chat-main">
        <?php if($active_chat_username): ?>
            <div class="chat-header">
                <div class="contact-avatar" style="background: linear-gradient(135deg, #f39c12, #d35400); width:35px; height:35px; font-size:1rem;"><?php echo strtoupper(substr($active_chat_username, 0, 1)); ?></div>
                @<?php echo htmlspecialchars($active_chat_username); ?>
                <a href="profile?user=<?php echo urlencode($active_chat_username); ?>" style="margin-left:auto; font-size:0.8rem; color:#f39c12; text-decoration:none;">Profili Gör</a>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <?php if(empty($active_messages)): ?>
                    <div style="text-align:center; color:#555; margin-top:50px;">Bu kullanıcıya ilk mesajı gönderin!</div>
                <?php else: ?>
                    <?php foreach($active_messages as $msg): ?>
                        <?php $is_me = ($msg['sender_id'] === $my_id); ?>
                        <div class="msg-bubble <?php echo $is_me ? 'msg-right' : 'msg-left'; ?>">
                            <?php if(!empty($msg['message'])): ?>
                                <div><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                            <?php endif; ?>
                            
                            <?php if(!empty($msg['file'])): ?>
                                <a href="<?php echo htmlspecialchars($msg['file']); ?>" download class="msg-file-link">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Dosya Eki İndir
                                </a>
                            <?php endif; ?>
                            
                            <div class="msg-time"><?php echo date('H:i', strtotime($msg['date'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="chat-input-area">
                <form action="index.php" method="POST" enctype="multipart/form-data" class="chat-form">
                    <input type="hidden" name="action" value="send_message">
                    <input type="hidden" name="receiver_username" value="<?php echo htmlspecialchars($active_chat_username); ?>">
                    
                    <label class="file-btn" title="Dosya Ekle">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        <input type="file" name="attachment">
                    </label>
                    
                    <input type="text" name="message" placeholder="Mesajınızı yazın..." autocomplete="off">
                    <button type="submit" class="send-btn">Gönder</button>
                </form>
            </div>
            
            <script>
                const chatBox = document.getElementById('chatMessages');
                if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
            </script>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#555;">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:20px; opacity:0.5;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <h2 style="margin:0; color: inherit;">Sohbete Başla</h2>
                <p>Mesajlaşmak için soldan bir kişi seçin veya arayın.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>