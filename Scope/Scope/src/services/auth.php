<style>
    /* Auth Modal Arka Planı */
    .auth-modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(10, 10, 12, 0.9); /* Arka planı biraz daha koyulaştırdım ki odak artsın */
        backdrop-filter: blur(8px);
        z-index: 2000;
        justify-content: center;
        align-items: center;
    }
    .auth-modal-overlay.active { display: flex; }

    /* Modal Kutusu */
    .auth-box {
        background-color: transparent;
        width: 100%;
        max-width: 400px;
        padding: 40px 20px;
        text-align: center;
        position: relative;
    }

    /* Kapatma Çarpısı */
    .close-modal {
        position: absolute;
        top: -20px; right: 10px;
        font-size: 35px;
        color: #888;
        cursor: pointer;
        transition: 0.2s;
        z-index: 2001; /* Çarpının her zaman üstte olduğundan emin olalım */
    }
    .close-modal:hover { color: white; transform: scale(1.1); }

    /* Başlıklar */
    .auth-title {
        font-family: "Georgia", "Times New Roman", serif;
        font-size: 2.2rem;
        font-weight: normal;
        margin-bottom: 10px;
        color: #ffffff;
    }

    .auth-subtitle {
        font-size: 0.9rem;
        color: #aaaaaa;
        margin-bottom: 30px;
    }

    /* Form Elemanları */
    .auth-form-group {
        margin-bottom: 15px;
        text-align: left;
    }
    .auth-form-group label {
        display: block;
        font-size: 0.85rem;
        color: #cccccc;
        margin-bottom: 8px;
    }
    .auth-input {
        width: 100%;
        box-sizing: border-box;
        padding: 12px 15px;
        background-color: #16161a;
        border: 1px solid #333;
        border-radius: 8px;
        color: white;
        font-size: 1rem;
        outline: none;
        transition: 0.3s;
    }
    .auth-input:focus { border-color: #8a365c; }

    /* Şifre Göz İkonu */
    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .auth-input.has-icon { padding-right: 45px; }
    .toggle-password {
        position: absolute;
        right: 15px;
        cursor: pointer;
        color: #888;
        font-size: 1.2rem;
        user-select: none;
    }

    /* Butonlar */
    .auth-submit-btn {
        width: 100%;
        padding: 14px;
        margin-top: 10px;
        border: none;
        border-radius: 25px;
        background: linear-gradient(90deg, #964027, #81254a, #964027);
        background-size: 200% 100%;
        color: #e0e0e0;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: 0.4s;
    }
    .auth-submit-btn:hover { background-position: 100% 0; color: white; }
    
    .toggle-auth { margin-top: 25px; font-size: 0.9rem; color: #888; }
    .toggle-auth a { color: white; text-decoration: none; font-weight: bold; cursor: pointer; }
    .toggle-auth a:hover { text-decoration: underline; }

    #registerForm { display: none; }
</style>

<div class="auth-modal-overlay" id="authModal">
    
    <div class="auth-box">
        <span class="close-modal" onclick="closeAuthModal()">&times;</span>
        
        <div id="loginForm">
            <h2 class="auth-title">Welcome back</h2>
            <p class="auth-subtitle">Please enter your details to sign in.</p>
            
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="auth-form-group">
                    <label>Username or Email</label>
                    <input type="text" name="login_id" class="auth-input" required>
                </div>
                <div class="auth-form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" class="auth-input has-icon" id="loginPass" required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('loginPass', this)">👁️</span>
                    </div>
                </div>
                <button type="submit" class="auth-submit-btn">Continue</button>
            </form>
            <div class="toggle-auth">
                Don't have an account? <a onclick="toggleAuthMode('register')">Sign up</a>
            </div>
        </div>

        <div id="registerForm">
            <h2 class="auth-title">Create account</h2>
            <p class="auth-subtitle">Join Scope. <span>Learn more</span> about our terms.</p>
            
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="auth-form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="auth-input" required>
                </div>
                <div class="auth-form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="auth-input" required>
                </div>
                <div class="auth-form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" class="auth-input has-icon" id="registerPass" required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('registerPass', this)">👁️</span>
                    </div>
                </div>
                <button type="submit" class="auth-submit-btn">Continue</button>
            </form>
            <div class="toggle-auth">
                Already have an account? <a onclick="toggleAuthMode('login')">Log in</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Şifre Göster/Gizle
    function togglePasswordVisibility(inputId, iconElement) {
        let input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            iconElement.textContent = "🙈";
        } else {
            input.type = "password";
            iconElement.textContent = "👁️";
        }
    }

    // Login/Register Geçişi
    function toggleAuthMode(mode) {
        if(mode === 'register') {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
        } else {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
        }
    }

    // Modalı Aç
    function openAuthModal(mode) {
        if(mode) toggleAuthMode(mode);
        else toggleAuthMode('login');
        document.getElementById('authModal').classList.add('active');
    }
    
    // Modalı Kapat (Sadece çarpı butonu bunu çağırıyor)
    function closeAuthModal() {
        document.getElementById('authModal').classList.remove('active');
    }

    // ARKA PLANA TIKLAYINCA KAPANMAYI ENGELLEMEK İÇİN:
    // Eski JS kodlarındaki window.onclick ve overlay üzerindeki tüm tıklama olaylarını temizledik.
</script>