<!-- _style.php — shared design system with dark mode -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ── RESET ── */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}

/* ── LIGHT THEME (default) ── */
:root {
  --brand:       #2563eb;
  --brand-dark:  #1d4ed8;
  --brand-light: #eff6ff;
  --green:       #16a34a;
  --green-bg:    #f0fdf4;
  --green-border:#bbf7d0;
  --red:         #dc2626;
  --red-bg:      #fef2f2;
  --red-border:  #fecaca;
  --amber:       #d97706;
  --amber-bg:    #fffbeb;
  --amber-border:#fde68a;
  --ink:         #111827;
  --muted:       #6b7280;
  --subtle:      #9ca3af;
  --bg:          #f3f4f8;
  --surface:     #ffffff;
  --surface2:    #f9fafb;
  --border:      #e5e7eb;
  --border-soft: #f1f1f4;
  --shadow-sm:   0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
  --shadow:      0 4px 12px rgba(0,0,0,.08);
  --shadow-lg:   0 10px 30px rgba(0,0,0,.12);
  --radius-sm:   8px;
  --radius:      14px;
  --radius-lg:   20px;
  --transition:  all .2s ease;
  --font-body:   'Nunito',sans-serif;
  --font-head:   'Sora',sans-serif;
}

/* ── DARK THEME ── */
[data-theme="dark"] {
  --brand:       #3b82f6;
  --brand-dark:  #2563eb;
  --brand-light: #1e3a5f;
  --green:       #22c55e;
  --green-bg:    #052e16;
  --green-border:#166534;
  --red:         #f87171;
  --red-bg:      #2d0a0a;
  --red-border:  #7f1d1d;
  --amber:       #fbbf24;
  --amber-bg:    #1c1207;
  --amber-border:#92400e;
  --ink:         #f1f5f9;
  --muted:       #94a3b8;
  --subtle:      #64748b;
  --bg:          #0f172a;
  --surface:     #1e293b;
  --surface2:    #162032;
  --border:      #334155;
  --border-soft: #1e293b;
  --shadow-sm:   0 1px 3px rgba(0,0,0,.3),0 1px 2px rgba(0,0,0,.2);
  --shadow:      0 4px 12px rgba(0,0,0,.3);
  --shadow-lg:   0 10px 30px rgba(0,0,0,.4);
}

/* ── BASE ── */
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--bg);color:var(--ink);line-height:1.6;min-height:100vh;-webkit-font-smoothing:antialiased;transition:background .25s,color .25s;}
h1,h2,h3,h4{font-family:var(--font-head);line-height:1.25;}

/* ── NAVBAR ── */
.navbar{position:sticky;top:0;z-index:200;display:flex;align-items:center;gap:.75rem;padding:.8rem 1.1rem;background:var(--surface);border-bottom:1px solid var(--border);box-shadow:var(--shadow-sm);}
.navbar-brand{font-family:var(--font-head);font-size:1.1rem;font-weight:700;color:var(--brand);text-decoration:none;display:flex;align-items:center;gap:.4rem;flex-shrink:0;letter-spacing:-.3px;}
.navbar-brand svg{width:20px;height:20px;}
.navbar-spacer{flex:1;}
.navbar-menu{display:flex;align-items:center;gap:.15rem;list-style:none;flex-shrink:0;}
.navbar-menu a,.nav-btn{display:inline-flex;align-items:center;gap:.3rem;font-family:var(--font-body);font-size:.8rem;font-weight:700;color:var(--muted);text-decoration:none;padding:.38rem .6rem;border-radius:var(--radius-sm);border:none;background:none;cursor:pointer;transition:var(--transition);white-space:nowrap;}
.navbar-menu a:hover,.nav-btn:hover{color:var(--brand);background:var(--brand-light);}
.navbar-menu a.active,.nav-btn.active{color:var(--brand);background:var(--brand-light);}
.nav-logout{color:var(--red)!important;}
.nav-logout:hover{background:var(--red-bg)!important;color:var(--red)!important;}

/* Dark mode toggle */
.dark-toggle{width:34px;height:34px;border-radius:50%;border:1.5px solid var(--border);background:var(--surface);color:var(--muted);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.95rem;transition:var(--transition);flex-shrink:0;}
.dark-toggle:hover{border-color:var(--brand);background:var(--brand-light);color:var(--brand);}

/* Book badge in nav */
.nav-book-pill{font-size:.75rem;font-weight:700;color:var(--brand);background:var(--brand-light);padding:.28rem .65rem;border-radius:99px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex-shrink:0;}

/* ── LAYOUT ── */
.page-wrap{max-width:1100px;margin:0 auto;padding:1rem .9rem 5rem;}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;font-family:var(--font-body);font-weight:700;font-size:.87rem;border:none;border-radius:var(--radius-sm);padding:.6rem 1.15rem;cursor:pointer;transition:var(--transition);text-decoration:none;white-space:nowrap;line-height:1;}
.btn svg{width:15px;height:15px;flex-shrink:0;}
.btn-primary{background:var(--brand);color:#fff;}
.btn-primary:hover{background:var(--brand-dark);transform:translateY(-1px);box-shadow:0 4px 12px rgba(37,99,235,.3);}
.btn-green{background:var(--green);color:#fff;}
.btn-green:hover{background:#15803d;transform:translateY(-1px);}
.btn-red{background:var(--red);color:#fff;}
.btn-red:hover{background:#b91c1c;transform:translateY(-1px);}
.btn-outline{background:var(--surface);color:var(--ink);border:1.5px solid var(--border);}
.btn-outline:hover{border-color:var(--brand);color:var(--brand);background:var(--brand-light);}
.btn-ghost{background:transparent;color:var(--muted);border:none;}
.btn-ghost:hover{background:var(--surface2);color:var(--ink);}
.btn-sm{font-size:.76rem;padding:.4rem .8rem;}
.btn-lg{font-size:.97rem;padding:.8rem 1.65rem;}
.btn-full{width:100%;}

/* ── CARD ── */
.card{background:var(--surface);border-radius:var(--radius);border:1px solid var(--border-soft);box-shadow:var(--shadow-sm);overflow:hidden;}

/* ── FORMS ── */
.form-group{margin-bottom:.9rem;}
.form-group label{display:block;font-size:.76rem;font-weight:700;color:var(--muted);margin-bottom:.3rem;letter-spacing:.3px;text-transform:uppercase;}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:.68rem .9rem;font-family:var(--font-body);font-size:.93rem;color:var(--ink);background:var(--surface2);border:1.5px solid var(--border);border-radius:var(--radius-sm);outline:none;transition:var(--transition);appearance:none;-webkit-appearance:none;}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(37,99,235,.12);background:var(--surface);}

/* ── ALERTS ── */
.alert{padding:.75rem 1rem;border-radius:var(--radius-sm);font-size:.85rem;font-weight:600;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
.alert-success{background:var(--green-bg);color:var(--green);border:1px solid var(--green-border);}
.alert-error{background:var(--red-bg);color:var(--red);border:1px solid var(--red-border);}
.alert-info{background:var(--brand-light);color:var(--brand);border:1px solid #bfdbfe;}
.alert-warning{background:var(--amber-bg);color:var(--amber);border:1px solid var(--amber-border);}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);z-index:500;display:flex;align-items:flex-end;justify-content:center;opacity:0;visibility:hidden;transition:opacity .22s ease,visibility .22s ease;}
@media(min-width:600px){.modal-overlay{align-items:center;}}
.modal-overlay.active{opacity:1;visibility:visible;}
.modal-box{background:var(--surface);border-radius:var(--radius-lg) var(--radius-lg) 0 0;width:100%;max-width:460px;max-height:92vh;overflow-y:auto;padding:1.35rem 1.1rem 2rem;transform:translateY(50px);transition:transform .3s cubic-bezier(.34,1.56,.64,1);position:relative;}
@media(min-width:600px){.modal-box{border-radius:var(--radius-lg);transform:scale(.93);} .modal-overlay.active .modal-box{transform:scale(1);}}
.modal-overlay.active .modal-box{transform:translateY(0) scale(1);}
.modal-drag{width:34px;height:4px;background:var(--border);border-radius:99px;margin:0 auto 1.1rem;display:block;}
@media(min-width:600px){.modal-drag{display:none;}}
.modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.1rem;padding-bottom:.75rem;border-bottom:1px solid var(--border-soft);}
.modal-head h2{font-size:1.08rem;}
.modal-close{width:30px;height:30px;border-radius:50%;border:none;background:var(--surface2);color:var(--muted);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.9rem;transition:var(--transition);text-decoration:none;flex-shrink:0;}
.modal-close:hover{background:var(--red-bg);color:var(--red);}

/* ── FILTER PILLS ── */
.filter-bar{background:var(--surface);border:1px solid var(--border-soft);border-radius:var(--radius);padding:.8rem .9rem;margin-bottom:.75rem;display:flex;flex-direction:column;gap:.65rem;}
.filter-top{display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;}
.filter-label-tag{font-size:.74rem;font-weight:700;color:var(--brand);background:var(--brand-light);padding:.22rem .6rem;border-radius:99px;display:inline-flex;align-items:center;gap:.3rem;white-space:nowrap;}
.filter-chips{display:flex;gap:.3rem;flex-wrap:wrap;}
.filter-chip{font-size:.72rem;font-weight:700;padding:.27rem .65rem;border-radius:99px;border:1.5px solid var(--border);background:var(--surface);color:var(--muted);text-decoration:none;cursor:pointer;transition:var(--transition);white-space:nowrap;}
.filter-chip:hover,.filter-chip.active{border-color:var(--brand);color:var(--brand);background:var(--brand-light);}
.filter-expand{display:none;flex-direction:column;gap:.6rem;}
.filter-expand.show{display:flex;}
.filter-expand input[type=date],.filter-expand select{font-family:var(--font-body);font-size:.82rem;padding:.38rem .65rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);outline:none;background:var(--surface2);color:var(--ink);flex:1;min-width:130px;appearance:none;}
.filter-expand input[type=date]:focus,.filter-expand select:focus{border-color:var(--brand);}

/* ── EMPTY STATE ── */
.empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.65rem;padding:2.5rem 1.5rem;text-align:center;color:var(--subtle);}
.empty-state svg{width:44px;height:44px;opacity:.35;}
.empty-state p{font-size:.87rem;line-height:1.5;}

/* ── SCROLLBAR ── */
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px;}

/* ── ANIMATIONS ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.fade-up  {animation:fadeUp .3s ease both;}
.fade-up-2{animation:fadeUp .3s .06s ease both;}
.fade-up-3{animation:fadeUp .3s .12s ease both;}
.fade-up-4{animation:fadeUp .3s .18s ease both;}
.fade-up-5{animation:fadeUp .3s .24s ease both;}

/* ── DARK MODE SCRIPT INLINE ── */
</style>
<script>
// Apply saved theme before paint to avoid flash
(function(){
  const t = localStorage.getItem('et_theme') || 'light';
  document.documentElement.setAttribute('data-theme', t);
})();
</script>
