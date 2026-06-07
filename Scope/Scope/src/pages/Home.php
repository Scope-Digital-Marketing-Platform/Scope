<div class="home-wrapper" style="width: 100%; padding-top: 10px; padding-bottom: 60px; position: relative; overflow: hidden; min-height: 100vh;">

    <div class="floating-particles">
        <div class="particle p-1">$</div> <div class="particle p-2">✕</div> <div class="particle p-3">◯</div> <div class="particle p-4">&lt;/&gt;</div> <div class="particle p-5">✦</div>
        <div class="particle p-11">{}</div> <div class="particle p-12">◯</div> <div class="particle p-13">$</div> <div class="particle p-14">✕</div> <div class="particle p-15">&lt;/&gt;</div>
        <div class="particle p-6">$</div> <div class="particle p-7">✕</div> <div class="particle p-8">◯</div> <div class="particle p-9">{}</div> <div class="particle p-10">✦</div>
        <div class="particle p-16">✕</div> <div class="particle p-17">&lt;/&gt;</div> <div class="particle p-18">◯</div> <div class="particle p-19">$</div> <div class="particle p-20">✦</div>
    </div>
    
    <style>
        .home-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 10; }
        
        .floating-particles { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
        .particle { position: absolute; color: rgba(255, 255, 255, 0.08); font-size: 28px; font-weight: 900; animation: fall linear infinite; }
        .particle:nth-child(odd) { color: rgba(243, 156, 18, 0.08); }
        @keyframes fall {
            0% { transform: translateY(-50px) rotate(0deg); opacity: 0; }
            10% { opacity: 1; } 90% { opacity: 1; }
            100% { transform: translateY(120vh) rotate(360deg); opacity: 0; }
        }
        
        /* Partikül Konumları ve Negatif Delay */
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

        /* Hero ve Paneller */
        .hero-section { text-align: center; margin-top: 20px; margin-bottom: 50px; }
        .hero-title { font-size: 3rem; font-weight: 800; margin-bottom: 15px; letter-spacing: -0.5px; transition: color 0.4s ease; }
        .hero-title span { color: #f39c12; }
        .hero-desc { color: #8b949e; font-size: 1.15rem; max-width: 600px; margin: 0 auto 30px auto; line-height: 1.6; transition: color 0.4s ease; }
        .btn-primary { background-color: #f39c12; color: #fff; padding: 12px 28px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: background 0.3s; box-shadow: 0 4px 14px rgba(243, 156, 18, 0.3); }
        .btn-primary:hover { background-color: #d68910; }
        .btn-secondary { background-color: transparent; color: #fff; padding: 12px 28px; border-radius: 6px; font-weight: 600; text-decoration: none; border: 1px solid rgba(255,255,255,0.15); transition: all 0.3s; }
        .btn-secondary:hover { background-color: rgba(255,255,255,0.05); }

        .home-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        @media (max-width: 992px) { .home-grid { grid-template-columns: 1fr; } }

        .panel { background: rgba(22, 27, 34, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 24px; backdrop-filter: blur(10px); transition: all 0.4s ease; }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .panel-title { font-size: 1.25rem; font-weight: 700; margin: 0; transition: color 0.4s ease; }
        
        .category-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; }

        .earn-panel { text-align: center; display: flex; flex-direction: column; align-items: center; height: 100%; }
        .earn-icon { width: 64px; height: 64px; background: #f39c12; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; box-shadow: 0 0 20px rgba(243, 156, 18, 0.4); }
        .earn-list { list-style: none; padding: 0; margin: 0 0 30px 0; text-align: left; width: 100%; }
        .earn-list li { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 0.9rem; color: #c9d1d9; transition: color 0.4s ease; }
        .earn-list li svg { color: #2ea043; }
        .earn-buttons { display: flex; gap: 10px; width: 100%; margin-top: auto; }
        .earn-buttons a { flex: 1; text-align: center; padding: 10px 0; font-size: 0.95rem; }

        /* LIGHT MODE (MAVİ & BEYAZ) */
        body.light-mode { background-color: #f0f4f8; color: #1e293b; background-image: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.06) 0%, transparent 40%), radial-gradient(circle at 100% 100%, rgba(168, 85, 247, 0.06) 0%, transparent 40%); }
        body.light-mode .panel { background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1); }
        body.light-mode .hero-title { color: #0f172a; }
        body.light-mode .hero-title span { color: #3b82f6; }
        body.light-mode .hero-desc { color: #64748b; }
        body.light-mode .panel-title { color: #0f172a; }
        body.light-mode .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        body.light-mode .btn-secondary { color: #475569; border-color: #cbd5e1; }
        body.light-mode .btn-secondary:hover { background-color: #f1f5f9; color: #0f172a; }
        body.light-mode .earn-icon { background: #3b82f6; box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); }
        body.light-mode .earn-list li { color: #475569; }
        body.light-mode .particle { color: rgba(59, 130, 246, 0.18); }
        body.light-mode .particle:nth-child(odd) { color: rgba(15, 23, 42, 0.12); }
    </style>

    <main class="home-container">
        <section class="hero-section">
            <h1 class="hero-title">Dijital yaratıcılığın yeni merkezi <span>Scope</span></h1>
            <p class="hero-desc">İhtiyacın olanı bul, uzmanlarla çalış ve hatta üret. 3D modellerden UI kitlerine kadar her şey tek platformda.</p>
            <div class="hero-buttons">
                <a href="store" class="btn-primary">Ürünleri Keşfet</a>
                <a href="community" class="btn-secondary">Konu Oluştur</a>
            </div>
        </section>

        <div class="home-grid">
            <div style="display: flex; flex-direction: column; gap: 30px;">
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Popüler Paketler</h2>
                        <a href="store" style="color: #8b949e; text-decoration: none; font-size: 0.9rem;">Tümünü Gör</a>
                    </div>
                    <div class="category-grid"></div>
                </div>
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">En Çok Satanlar</h2>
                    </div>
                    <div class="product-grid"></div>
                </div>
            </div>

            <div>
                <div class="panel earn-panel">
                    <div class="earn-icon">
                        <svg width="32" height="32" fill="none" stroke="#ffffff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="panel-title" style="margin-bottom: 10px;">Gelir Elde Et</h2>
                    <p style="color: #8b949e; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.5;">Kendi dijital varlıklarını vitrine çıkar veya yeteneklerini Scope topluluğuna sunarak freelance projelerde yer al.</p>
                    <ul class="earn-list">
                        <li><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Mağazanı hemen oluştur</li>
                        <li><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Freelance ilanlarına başvur</li>
                        <li><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Sıfır komisyon fırsatı (Beta)</li>
                    </ul>
                    <div class="earn-buttons">
                        <a href="createproduct" class="btn-primary">Ürün Oluştur</a>
                        <a href="freelancehub" class="btn-secondary" style="border-color: #333; color: inherit;">İlanlara Bak</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>