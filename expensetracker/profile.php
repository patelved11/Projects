<?php
session_name("ExpenseTracker"); session_start();
include "db.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { header("location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Token Generation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_token'])) {
    $new_token = bin2hex(random_bytes(32));
    $stmt = $conn->prepare("UPDATE users SET api_token = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_token, $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Token generated successfully! Please copy it now.";
        $_SESSION['show_token'] = $new_token;
    } else {
        $_SESSION['error'] = "Error generating token.";
    }
    header("Location: profile.php");
    exit;
}

// Handle Token Revocation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['revoke_token'])) {
    $stmt = $conn->prepare("UPDATE users SET api_token = NULL WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Token revoked successfully.";
    } else {
        $_SESSION['error'] = "Error revoking token.";
    }
    header("Location: profile.php");
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT api_token FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$has_token = !empty($user_data['api_token']);

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
$show_token = '';
if (isset($_SESSION['show_token'])) {
    $show_token = $_SESSION['show_token'];
    unset($_SESSION['show_token']);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <?php include '_style.php'; ?>
    <title>Profile - ExpenseTracker</title>
    <style>
        .profile-container { max-width: 800px; margin: 2rem auto; padding: 0 1.5rem; }
        .token-box {
            background: var(--surface2);
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            margin-top: 1rem;
        }
        .token-display {
            font-family: monospace;
            background: var(--surface);
            padding: 1rem;
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-sm);
            word-break: break-all;
            margin: 1rem 0;
            font-size: 1.1rem;
            color: var(--brand);
            user-select: all;
        }
    </style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
    <span class="hide-xs">ET</span>
  </a>
  <div class="navbar-spacer"></div>
  <ul class="navbar-menu">
    <li><a href="dashboard.php" class="nav-btn">Dashboard</a></li>
    <li><a href="books.php" class="nav-btn">Books</a></li>
    <li><a href="profile.php" class="nav-btn active">Profile</a></li>
    <li><a href="logout.php" class="nav-btn nav-logout">Logout</a></li>
  </ul>
  <button class="dark-toggle" id="darkToggle">🌙</button>
</nav>

<div class="profile-container fade-up">
    <h2>Profile & Settings</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error">✗ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card card-body" style="margin-top: 1.5rem;">
        <h3>API Access</h3>
        <p class="text-muted" style="margin-bottom: 1rem;">Generate an API token to allow other applications (like TradeJournal) to sync data directly to your ExpenseTracker books.</p>
        
        <div class="token-box">
            <?php if ($show_token): ?>
                <div class="alert alert-success" style="margin-bottom: 1rem;">
                    <strong>Warning:</strong> Save this token now! It will not be shown again.
                </div>
                <p>Your new API Token:</p>
                <div class="token-display"><?= htmlspecialchars($show_token) ?></div>
                <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($show_token) ?>'); alert('Copied to clipboard!');" class="btn btn-outline btn-sm">Copy to Clipboard</button>
            <?php else: ?>
                <?php if ($has_token): ?>
                    <p>Current Token: <strong>****************************************<?= htmlspecialchars(substr($user_data['api_token'], -8)) ?></strong></p>
                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Generating a new token will invalidate the old one. Are you sure?');">
                            <button type="submit" name="generate_token" class="btn btn-primary">Generate New Token</button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to revoke your API access?');">
                            <button type="submit" name="revoke_token" class="btn btn-red">Revoke Token</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>No active API token.</p>
                    <form method="POST" style="margin-top: 1rem;">
                        <button type="submit" name="generate_token" class="btn btn-primary">Generate Token</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const darkBtn = document.getElementById('darkToggle');
function applyTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
  darkBtn.textContent = t === 'dark' ? '☀️' : '🌙';
  localStorage.setItem('et_theme', t);
}
applyTheme(localStorage.getItem('et_theme') || 'light');
darkBtn.addEventListener('click', () => {
  applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
});

setTimeout(() => {
  document.querySelectorAll('.alert:not(.alert-success)').forEach(el => {
    if(el.textContent.includes('Warning:')) return;
    el.style.transition = 'opacity .4s'; el.style.opacity = '0';
    setTimeout(() => el.remove(), 400);
  });
}, 3000);
</script>
</body>
</html>
