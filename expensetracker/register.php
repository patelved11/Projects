<?php
session_name("ExpenseTracker"); session_start();
include "db.php";
$alert_message = '';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: books.php"); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $alert_message = "An account with this email already exists.";
    } else {
        $plain_password = $password;
        $stmt_insert = $conn->prepare("INSERT INTO `users` (`name`, `email`, `password`) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sss", $name, $email, $plain_password);
        if ($stmt_insert->execute()) {
            $_SESSION['success_message'] = "Account created! Please sign in.";
            header("location: login.php"); exit;
        } else {
            $alert_message = "Could not register. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '_style.php'; ?>
    <title>Create Account – ExpenseTracker</title>
    <style>
        body { background: var(--bg); display: flex; flex-direction: column; min-height: 100vh; }
        .auth-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .auth-card { background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); width: 100%; max-width: 420px; padding: 2rem 1.75rem; border: 1px solid var(--border-soft); }
        .auth-title { font-family: var(--font-head); font-size: 1.55rem; font-weight: 700; color: var(--ink); margin-bottom: .35rem; text-align: center; }
        .auth-sub { font-size: .85rem; color: var(--muted); text-align: center; margin-bottom: 1.75rem; }
        .auth-footer { text-align: center; margin-top: 1.25rem; font-size: .85rem; color: var(--muted); }
        .auth-footer a { color: var(--brand); font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap svg { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--subtle); pointer-events: none; }
        .input-icon-wrap input { padding-left: 2.5rem; }
        .pw-toggle { position: absolute; right: .85rem; bottom: .75rem; background: none; border: none; cursor: pointer; color: var(--subtle); display: flex; align-items: center; }
        .pw-toggle:hover { color: var(--brand); }
        .pw-toggle svg { width: 17px; height: 17px; }
    </style>
</head>
<body>
<nav class="navbar" style="justify-content:space-between;">
    <a href="index.php" class="navbar-brand">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
        ExpenseTracker
    </a>
    <a href="login.php" class="btn btn-outline btn-sm">Sign In</a>
</nav>

<div class="auth-wrap">
    <div class="auth-card fade-up">
        <div class="auth-title">Create your account</div>
        <div class="auth-sub">Free forever. No credit card required.</div>

        <?php if (!empty($alert_message)): ?>
            <div class="alert alert-error">✗ <?= htmlspecialchars($alert_message) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="on">
            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="input-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" id="name" name="name" placeholder="Your full name" required autocomplete="name"
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email"
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>
            <div class="form-group" style="position:relative;">
                <label for="password">Password</label>
                <div class="input-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                </div>
                <button type="button" class="pw-toggle" onclick="togglePw(this)" aria-label="Toggle password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">Create Account</button>
        </form>

        <div class="auth-footer">Already have an account? <a href="login.php">Sign in</a></div>
    </div>
</div>

<script>
function togglePw(btn) {
    const inp = btn.closest('.form-group').querySelector('input');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
