<?php
session_name("ExpenseTracker"); session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '_style.php'; ?>
    <title>ExpenseTracker – Free Online Expense Tracker</title>
    <meta name="description" content="ExpenseTracker is a free online expense tracker to manage income and expenses. Track daily spending, view reports, and control your monthly budget easily.">
    <style>
        body { background: #0f172a; color: #f1f5f9; }
        .hero-logo { font-family: var(--font-head); font-size: 1.25rem; font-weight: 700; color: #60a5fa; display: flex; align-items: center; gap: .5rem; }
        .hero-nav-links { display: flex; gap: .5rem; }
        .hero-nav-links a { font-size: .85rem; font-weight: 600; padding: .5rem 1rem; border-radius: var(--radius-sm); text-decoration: none; transition: var(--transition); }
        .link-ghost { color: #94a3b8; }
        .link-ghost:hover { color: #f1f5f9; background: rgba(255,255,255,.06); }
        .link-solid { background: #2563eb; color: #fff; }
        .link-solid:hover { background: #1d4ed8; }
        .hero { max-width: 1100px; margin: 0 auto; padding: 4rem 1.5rem 3rem; text-align: center; }
        .hero-tag { display: inline-flex; align-items: center; gap: .4rem; font-size: .78rem; font-weight: 700; color: #60a5fa; background: rgba(96,165,250,.12); border: 1px solid rgba(96,165,250,.25); padding: .35rem .85rem; border-radius: 99px; letter-spacing: .4px; text-transform: uppercase; margin-bottom: 1.5rem; }
        .hero h1 { font-family: var(--font-head); font-size: clamp(2rem, 7vw, 3.6rem); font-weight: 700; line-height: 1.15; letter-spacing: -.5px; color: #f1f5f9; margin-bottom: 1.25rem; }
        .hero h1 span { color: #60a5fa; }
        .hero p { font-size: clamp(.95rem, 2.5vw, 1.1rem); color: #94a3b8; max-width: 560px; margin: 0 auto 2.5rem; }
        .hero-btns { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }
        .hero-btn-primary { display: inline-flex; align-items: center; gap: .5rem; font-family: var(--font-body); font-size: 1rem; font-weight: 700; color: #fff; background: #2563eb; padding: .85rem 2rem; border-radius: var(--radius-sm); text-decoration: none; transition: var(--transition); box-shadow: 0 4px 20px rgba(37,99,235,.4); }
        .hero-btn-primary:hover { background: #1d4ed8; transform: translateY(-2px); }
        .hero-btn-ghost { display: inline-flex; align-items: center; gap: .5rem; font-family: var(--font-body); font-size: 1rem; font-weight: 700; color: #94a3b8; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); padding: .85rem 2rem; border-radius: var(--radius-sm); text-decoration: none; transition: var(--transition); }
        .hero-btn-ghost:hover { color: #f1f5f9; background: rgba(255,255,255,.1); }
        .hero-btn-apk { display: inline-flex; align-items: center; gap: .5rem; font-family: var(--font-body); font-size: 1rem; font-weight: 700; color: #4ade80; background: rgba(22,163,74,.12); border: 1px solid rgba(74,222,128,.25); padding: .85rem 2rem; border-radius: var(--radius-sm); text-decoration: none; transition: var(--transition); }
        .hero-btn-apk:hover { background: rgba(22,163,74,.22); color: #86efac; transform: translateY(-2px); }
        .apk-note { margin-top: 1rem; font-size: .78rem; color: #475569; }
        .features { max-width: 1100px; margin: 3rem auto 0; padding: 0 1.5rem 4rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
        .feat-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: var(--radius); padding: 1.5rem; transition: var(--transition); }
        .feat-card:hover { background: rgba(255,255,255,.07); transform: translateY(-3px); }
        .feat-icon { width: 44px; height: 44px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.3rem; }
        .feat-card h3 { font-family: var(--font-head); font-size: .97rem; color: #f1f5f9; margin-bottom: .4rem; }
        .feat-card p  { font-size: .82rem; color: #64748b; line-height: 1.5; }
        footer { text-align: center; padding: 1.5rem; font-size: .8rem; color: #334155; border-top: 1px solid rgba(255,255,255,.05); }
    </style>
</head>
<body>
<nav style="display:flex; align-items:center; justify-content:space-between; padding:.85rem 1.25rem; border-bottom:1px solid rgba(255,255,255,.06); background:rgba(15,23,42,.9); backdrop-filter:blur(12px); position:sticky; top:0; z-index:100;">
    <div class="hero-logo">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
        ExpenseTracker
    </div>
    <div class="hero-nav-links">
        <a href="login.php" class="link-ghost">Sign In</a>
        <a href="register.php" class="link-solid">Get Started</a>
    </div>
</nav>

<div class="hero">
    <div class="hero-tag">✦ Free &amp; Open — No Ads</div>
    <h1>Track every rupee,<br><span>stay in control</span></h1>
    <p>A clean, fast expense tracker for daily use. Add income and expenses in seconds, see your balance instantly, and understand where your money goes.</p>
    <div class="hero-btns">
        <a href="register.php" class="hero-btn-primary">+ Create Free Account</a>
        <a href="login.php" class="hero-btn-ghost">Sign In →</a>
    <!--    <a href="downloads/ExpenseTracker.apk" class="hero-btn-apk" download>  -->
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2z" style="display:none"/>
                <rect x="5" y="2" width="14" height="20" rx="2"/>
                <path d="M12 11v6M9 14l3 3 3-3"/>
            </svg>
            Download Android App
        </a>
    </div>
    <p class="apk-note">📱 Android APK &nbsp;·&nbsp; Enable "Install from unknown sources" to install</p>
</div>

<div class="features">
    <div class="feat-card"><div class="feat-icon" style="background:rgba(37,99,235,.15);color:#60a5fa;">📒</div><h3>Multiple Books</h3><p>Organize finances by creating separate books — personal, business, household.</p></div>
    <div class="feat-card"><div class="feat-icon" style="background:rgba(22,163,74,.12);color:#4ade80;">₹</div><h3>Cash In / Cash Out</h3><p>Add income and expenses in a tap. Running balance is always visible and auto-calculated.</p></div>
    <div class="feat-card"><div class="feat-icon" style="background:rgba(217,119,6,.12);color:#fbbf24;">📊</div><h3>Financial Year Filter</h3><p>Defaults to the current Indian financial year (Apr–Mar). Switch to any custom date range anytime.</p></div>
    <div class="feat-card"><div class="feat-icon" style="background:rgba(220,38,38,.1);color:#f87171;">🏷️</div><h3>Category Reports</h3><p>See exactly where money goes with visual pie charts broken down by categories.</p></div>
    <div class="feat-card"><div class="feat-icon" style="background:rgba(139,92,246,.1);color:#a78bfa;">📱</div><h3>Mobile First</h3><p>Designed for your phone first — smooth sheet modals, large tap targets, bottom-friendly layout.</p></div>
    <div class="feat-card"><div class="feat-icon" style="background:rgba(20,184,166,.1);color:#2dd4bf;">🔒</div><h3>Secure & Private</h3><p>Your data stays yours. Session-based auth. No tracking, no selling data.</p></div>
</div>

<footer>© <?php echo date('Y'); ?> ExpenseTracker &nbsp;·&nbsp; Developed by Ved Patel</footer>
</body>
</html>