<?php
session_name("ExpenseTracker"); session_start();
include "db.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { header("location: login.php"); exit; }
if (!isset($_SESSION['book_id'])) { header("location: books.php"); exit; }

$user_id  = $_SESSION['user_id'];
$book_id  = $_SESSION['book_id'];
$bookName = $_SESSION['bookname'] ?? 'My Book';
$today    = date("Y-m-d");

// ── BALANCE RECALC ─────────────────────────────────────────────────────────
function doRecalc($conn, $user_id, $book_id) {
    $balance = 0;
    $r = $conn->prepare("SELECT id,income,expense FROM expenses WHERE user_id=? AND book_id=? ORDER BY date,id");
    $r->bind_param("ii", $user_id, $book_id);
    $r->execute();
    $result = $r->get_result();
    while ($row = $result->fetch_assoc()) {
        $balance += floatval($row['income']) - floatval($row['expense']);
        $u = $conn->prepare("UPDATE expenses SET balance=? WHERE id=?");
        $u->bind_param("di", $balance, $row['id']);
        $u->execute();
    }
}

// ── POST HANDLERS ──────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['delete_id'])) {
        $del_id = (int)$_POST['delete_id'];
        $s = $conn->prepare("DELETE FROM expenses WHERE id=? AND user_id=? AND book_id=?");
        $s->bind_param("iii", $del_id, $user_id, $book_id);
        $s->execute();
        if ($s->affected_rows > 0) {
            doRecalc($conn, $user_id, $book_id);
            $_SESSION['success'] = "Transaction deleted.";
        }
        header("Location: " . $_SERVER['PHP_SELF']); exit;
    }

    if (isset($_POST['edit_id'])) {
        $eid  = (int)$_POST['edit_id'];
        $date = $_POST['date'] ?? $today;
        $desc = trim($_POST['description'] ?? '');
        $cat  = $_POST['category_name'] ?? '';
        $amt  = abs(floatval($_POST['amount'] ?? 0));
        $type = $_POST['txn_type'] ?? 'income';
        $inc  = $type === 'income'  ? $amt : 0;
        $exp  = $type === 'expense' ? $amt : 0;
        $s = $conn->prepare("UPDATE expenses SET date=?,description=?,category_name=?,income=?,expense=? WHERE id=? AND user_id=? AND book_id=?");
        $s->bind_param("sssddiii", $date, $desc, $cat, $inc, $exp, $eid, $user_id, $book_id);
        $s->execute();
        doRecalc($conn, $user_id, $book_id);
        $_SESSION['success'] = "Transaction updated.";
        header("Location: " . $_SERVER['PHP_SELF']); exit;
    }

    if (isset($_POST['savecategory'])) {
        $nc = trim($_POST['category'] ?? '');
        if ($nc) {
            $s = $conn->prepare("INSERT INTO categories(categories,users_id) VALUES(?,?)");
            $s->bind_param("si", $nc, $user_id);
            $s->execute();
            $_SESSION['success'] = "Category added!";
        }
        header("Location: " . $_SERVER['PHP_SELF']); exit;
    }

    if (isset($_POST['savein']) || isset($_POST['saveout'])) {
        $date = $_POST['date'] ?? $today;
        $desc = $_POST['description'] ?? '';
        $cat  = $_POST['category_name'] ?? '';
        $amt  = $_POST['amount'] ?? 0;
        $isin = isset($_POST['savein']);
        $inc  = $isin ? $amt : 0;
        $exp  = !$isin ? $amt : 0;
        $s = $conn->prepare("INSERT INTO expenses(user_id,book_id,date,description,category_name,income,expense,balance) VALUES(?,?,?,?,?,?,?,0)");
        $s->bind_param("iisssdd", $user_id, $book_id, $date, $desc, $cat, $inc, $exp);
        if ($s->execute()) {
            doRecalc($conn, $user_id, $book_id);
            $_SESSION['success'] = $isin ? "Income added successfully." : "Expense added successfully.";
        } else {
            $_SESSION['error'] = "Error: " . $s->error;
        }
        header("Location: " . $_SERVER['PHP_SELF']); exit;
    }
}

// ── FILTER ─────────────────────────────────────────────────────────────────
function getCurrentFY() {
    $m = (int)date('n'); $y = (int)date('Y');
    return $m >= 4
        ? ['start' => "$y-04-01", 'end' => ($y+1)."-03-31", 'label' => "FY $y-".($y+1)]
        : ['start' => ($y-1)."-04-01", 'end' => "$y-03-31", 'label' => "FY ".($y-1)."-$y"];
}
function getFYOptions() {
    $m = (int)date('n'); $y = (int)date('Y');
    $csy = ($m >= 4) ? $y : $y - 1; $opts = [];
    for ($i = 0; $i <= 4; $i++) {
        $sy = $csy - $i; $ey = $sy + 1;
        $opts[] = ['start' => "$sy-04-01", 'end' => "$ey-03-31", 'label' => "FY $sy-$ey", 'value' => "$sy-04-01|$ey-03-31"];
    }
    return $opts;
}

$fy          = getCurrentFY();
$filter_mode = $_GET['filter'] ?? 'all';
$date_from   = '2000-01-01'; $date_to = '2099-12-31'; $filter_label = 'All Time';

if ($filter_mode === 'fy') {
    $date_from = $fy['start']; $date_to = $fy['end']; $filter_label = $fy['label'];
} elseif ($filter_mode === 'month') {
    $date_from = date('Y-m-01'); $date_to = date('Y-m-t'); $filter_label = date('F Y');
} elseif ($filter_mode === 'custom') {
    $date_from = preg_replace('/[^0-9\-]/', '', $_GET['from'] ?? $fy['start']);
    $date_to   = preg_replace('/[^0-9\-]/', '', $_GET['to']   ?? $fy['end']);
    $filter_label = date('d M Y', strtotime($date_from)) . ' to ' . date('d M Y', strtotime($date_to));
} elseif ($filter_mode === 'fysel') {
    $fyval = $_GET['fyval'] ?? '';
    if ($fyval && strpos($fyval, '|') !== false) {
        [$date_from, $date_to] = explode('|', $fyval);
        $date_from = preg_replace('/[^0-9\-]/', '', $date_from);
        $date_to   = preg_replace('/[^0-9\-]/', '', $date_to);
    }
    $filter_label = $_GET['fylabel'] ?? 'Selected FY';
}

// ── FETCH DATA ─────────────────────────────────────────────────────────────
$st = $conn->prepare("SELECT SUM(income) ti, SUM(expense) te FROM expenses WHERE user_id=? AND book_id=? AND date BETWEEN ? AND ?");
$st->bind_param("iiss", $user_id, $book_id, $date_from, $date_to);
$st->execute();
$sum = $st->get_result()->fetch_assoc();
$total_income  = floatval($sum['ti'] ?? 0);
$total_expense = floatval($sum['te'] ?? 0);
$net           = $total_income - $total_expense;

$bl = $conn->prepare("SELECT balance FROM expenses WHERE user_id=? AND book_id=? ORDER BY date DESC, id DESC LIMIT 1");
$bl->bind_param("ii", $user_id, $book_id);
$bl->execute();
$bl_row       = $bl->get_result()->fetch_assoc();
$last_balance = floatval($bl_row['balance'] ?? 0);

$sd = $conn->prepare("SELECT * FROM expenses WHERE user_id=? AND book_id=? AND date BETWEEN ? AND ? ORDER BY date DESC, id DESC");
$sd->bind_param("iiss", $user_id, $book_id, $date_from, $date_to);
$sd->execute();
$rows = $sd->get_result()->fetch_all(MYSQLI_ASSOC);

$sc = $conn->prepare("SELECT * FROM categories WHERE users_id IN(1,?) ORDER BY categories ASC");
$sc->bind_param("i", $user_id);
$sc->execute();
$categories = $sc->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<?php include '_style.php'; ?>
<title><?= htmlspecialchars($bookName) ?> - ExpenseTracker</title>
<style>
.sum-row{display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin-bottom:.8rem;}
.sum-card{background:var(--surface);border:1.5px solid var(--border-soft);border-radius:var(--radius);padding:1rem .85rem;position:relative;overflow:hidden;transition:var(--transition);}
.sum-card:hover{box-shadow:var(--shadow);transform:translateY(-1px);}
.sum-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
.sum-card.balance::before{background:linear-gradient(90deg,var(--brand),#818cf8);}
.sum-card.income::before{background:linear-gradient(90deg,var(--green),#86efac);}
.sum-card.expense::before{background:linear-gradient(90deg,var(--red),#fca5a5);}
.sum-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--subtle);margin-bottom:.3rem;}
.sum-val{font-family:var(--font-head);font-size:clamp(.9rem,3.5vw,1.4rem);font-weight:700;line-height:1;}
.sum-card.balance .sum-val{color:var(--brand);}
.sum-card.income  .sum-val{color:var(--green);}
.sum-card.expense .sum-val{color:var(--red);}
.sum-card.neg-bal .sum-val{color:var(--red)!important;}
.sum-sub{font-size:.66rem;color:var(--subtle);margin-top:.28rem;}

.big-actions{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.8rem;}
.big-btn{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.9rem;border-radius:var(--radius);border:2px solid;cursor:pointer;background:none;font-family:var(--font-body);font-weight:800;font-size:.95rem;transition:var(--transition);}
.big-btn.in-btn{border-color:var(--green-border);color:var(--green);}
.big-btn.in-btn:hover{background:var(--green-bg);transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,.15);}
.big-btn.out-btn{border-color:var(--red-border);color:var(--red);}
.big-btn.out-btn:hover{background:var(--red-bg);transform:translateY(-1px);box-shadow:0 4px 12px rgba(220,38,38,.15);}

.filter-bar{background:var(--surface);border:1.5px solid var(--border-soft);border-radius:var(--radius);padding:.75rem .9rem;margin-bottom:.7rem;}
.filter-top{display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;}
.filter-label-tag{font-size:.74rem;font-weight:700;color:var(--brand);background:var(--brand-light);padding:.22rem .6rem;border-radius:99px;white-space:nowrap;}
.filter-chips{display:flex;gap:.3rem;flex-wrap:wrap;}
.filter-chip{font-size:.72rem;font-weight:700;padding:.27rem .65rem;border-radius:99px;border:1.5px solid var(--border);background:var(--surface);color:var(--muted);text-decoration:none;cursor:pointer;transition:var(--transition);white-space:nowrap;}
.filter-chip:hover,.filter-chip.active{border-color:var(--brand);color:var(--brand);background:var(--brand-light);}
.filter-expand{display:none;flex-direction:column;gap:.5rem;margin-top:.55rem;}
.filter-expand.show{display:flex;}
.filter-expand input[type=date],.filter-expand select{font-family:var(--font-body);font-size:.82rem;padding:.38rem .65rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);outline:none;background:var(--surface2);color:var(--ink);flex:1;appearance:none;}
.filter-expand input[type=date]:focus,.filter-expand select:focus{border-color:var(--brand);}

.search-wrap{position:relative;margin-bottom:.6rem;}
.search-wrap svg{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--subtle);pointer-events:none;}
#searchInput{width:100%;padding:.62rem .9rem .62rem 2.4rem;font-family:var(--font-body);font-size:.88rem;color:var(--ink);background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-sm);outline:none;transition:var(--transition);}
#searchInput:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(37,99,235,.1);}
#searchInput::placeholder{color:var(--subtle);}
.search-clear{position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--subtle);display:none;font-size:1rem;padding:.2rem;}
.search-clear.visible{display:block;}

.cat-chips{display:flex;gap:.3rem;overflow-x:auto;padding-bottom:.3rem;margin-bottom:.6rem;scrollbar-width:none;}
.cat-chips::-webkit-scrollbar{display:none;}
.cat-chip{font-size:.72rem;font-weight:700;padding:.25rem .65rem;border-radius:99px;border:1.5px solid var(--border);background:var(--surface);color:var(--muted);cursor:pointer;transition:var(--transition);white-space:nowrap;flex-shrink:0;}
.cat-chip:hover,.cat-chip.active{border-color:var(--brand);color:var(--brand);background:var(--brand-light);}

.action-row{display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.6rem;flex-wrap:wrap;}
.action-row-left{display:flex;align-items:center;gap:.4rem;}
.txn-count{font-size:.78rem;font-weight:700;color:var(--subtle);}
.export-row{display:flex;gap:.35rem;}

.table-wrap{background:var(--surface);border:1px solid var(--border-soft);border-radius:var(--radius);overflow:hidden;}
.tbl{width:100%;border-collapse:collapse;}
.tbl th{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--subtle);background:var(--surface2);padding:.7rem 1rem;text-align:left;border-bottom:1px solid var(--border);white-space:nowrap;}
.tbl td{padding:.8rem 1rem;border-bottom:1px solid var(--border-soft);font-size:.86rem;vertical-align:middle;}
.tbl tbody tr:last-child td{border-bottom:none;}
.tbl tbody tr:hover{background:var(--surface2);}
.td-cat{font-size:.7rem;background:var(--surface2);padding:.12rem .45rem;border-radius:99px;color:var(--muted);font-weight:700;border:1px solid var(--border);}
.td-in{color:var(--green);font-weight:700;}
.td-out{color:var(--red);font-weight:700;}
.td-zero{color:var(--border);}
.td-bal{font-weight:700;color:var(--brand);}
.td-bal.neg{color:var(--red);}
.tbl-actions{display:flex;gap:.3rem;}
.tbl-action-btn{width:28px;height:28px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface2);color:var(--muted);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.75rem;transition:var(--transition);}
.tbl-action-btn.edit-btn:hover{border-color:var(--brand);background:var(--brand-light);color:var(--brand);}
.tbl-action-btn.del-btn:hover{border-color:var(--red);background:var(--red-bg);color:var(--red);}
.confirm-del{display:none;background:var(--red-bg);border:1px solid var(--red-border);border-radius:var(--radius-sm);padding:.5rem .75rem;gap:.4rem;align-items:center;flex-wrap:wrap;margin-top:.25rem;font-size:.8rem;color:var(--red);font-weight:600;}
.confirm-del.show{display:flex;}

.txn-list{display:flex;flex-direction:column;gap:.4rem;}
.txn-card{background:var(--surface);border:1px solid var(--border-soft);border-radius:var(--radius-sm);padding:.75rem .85rem;display:flex;align-items:center;gap:.7rem;transition:var(--transition);}
.txn-card:hover{border-color:var(--border);box-shadow:var(--shadow-sm);}
.txn-dot{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;font-weight:800;}
.txn-dot.in{background:var(--green-bg);color:var(--green);}
.txn-dot.out{background:var(--red-bg);color:var(--red);}
.txn-info{flex:1;min-width:0;}
.txn-desc{font-size:.87rem;font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.txn-meta{display:flex;align-items:center;gap:.35rem;margin-top:.06rem;flex-wrap:wrap;}
.txn-date{font-size:.7rem;color:var(--subtle);}
.txn-cat{font-size:.65rem;font-weight:700;background:var(--surface2);color:var(--muted);padding:.06rem .4rem;border-radius:99px;border:1px solid var(--border);}
.txn-right{display:flex;flex-direction:column;align-items:flex-end;gap:.18rem;flex-shrink:0;}
.txn-amt{font-family:var(--font-head);font-size:.92rem;font-weight:700;}
.txn-amt.in{color:var(--green);}
.txn-amt.out{color:var(--red);}
.txn-bal{font-size:.67rem;color:var(--subtle);}
.txn-bal.neg{color:var(--red);}
.txn-actions{display:flex;gap:.2rem;flex-shrink:0;}
.txn-action-btn{width:28px;height:28px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface2);color:var(--muted);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.75rem;transition:var(--transition);}
.txn-action-btn.edit-btn:hover{border-color:var(--brand);background:var(--brand-light);color:var(--brand);}
.txn-action-btn.del-btn:hover{border-color:var(--red);background:var(--red-bg);color:var(--red);}

.desktop-only{display:table;}
.mobile-only{display:none;}
@media(max-width:700px){.desktop-only{display:none;}.mobile-only{display:block;}}
@media(max-width:400px){.hide-xs{display:none;}}
@media(max-width:600px){.hide-sm{display:none;}}
</style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
    <span class="hide-xs">ET</span>
  </a>
  <span class="nav-book-pill" title="<?= htmlspecialchars($bookName) ?>">📒 <?= htmlspecialchars($bookName) ?></span>
  <div class="navbar-spacer"></div>
  <ul class="navbar-menu">
    <li><a href="chart.php" class="nav-btn">Charts</a></li>
    <li><a href="books.php" class="nav-btn">Books</a></li>
    <li><a href="profile.php" class="nav-btn">Profile</a></li>
    <li><a href="logout.php" class="nav-btn nav-logout">Logout</a></li>
</ul>
  <button class="dark-toggle" id="darkToggle">🌙</button>
</nav>

<div class="page-wrap">

<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success fade-up">✓ <?= htmlspecialchars($_SESSION['success']) ?></div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-error fade-up">✗ <?= htmlspecialchars($_SESSION['error']) ?></div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- SUMMARY -->
<div class="sum-row fade-up">
  <div class="sum-card balance <?= $last_balance < 0 ? 'neg-bal' : '' ?>">
    <div class="sum-label">Balance</div>
    <div class="sum-val">&#8377;<?= number_format(abs($last_balance), 0) ?><?= $last_balance < 0 ? ' <small style="font-size:.55em">DR</small>' : '' ?></div>
    <div class="sum-sub">Running total</div>
  </div>
  <div class="sum-card income">
    <div class="sum-label">Income</div>
    <div class="sum-val">&#8377;<?= number_format($total_income, 0) ?></div>
    <div class="sum-sub"><?= htmlspecialchars($filter_label) ?></div>
  </div>
  <div class="sum-card expense">
    <div class="sum-label">Expense</div>
    <div class="sum-val">&#8377;<?= number_format($total_expense, 0) ?></div>
    <div class="sum-sub"><?= $net >= 0 ? '&#8377;'.number_format($net,0).' saved' : '&#8377;'.number_format(abs($net),0).' over' ?></div>
  </div>
</div>

<!-- CASH IN / OUT -->
<div class="big-actions fade-up-2">
  <button class="big-btn in-btn" onclick="openModal('incomeModal')">&#8593; Cash In</button>
  <button class="big-btn out-btn" onclick="openModal('expenseModal')">&#8595; Cash Out</button>
</div>

<!-- FILTER BAR -->
<div class="filter-bar fade-up-3">
  <div class="filter-top">
    <span class="filter-label-tag">&#128197; <?= htmlspecialchars($filter_label) ?></span>
    <div class="filter-chips">
      <a href="?filter=all"   class="filter-chip <?= $filter_mode==='all'   ?'active':'' ?>">All Time</a>
      <a href="?filter=fy"    class="filter-chip <?= $filter_mode==='fy'    ?'active':'' ?>">This FY</a>
      <a href="?filter=month" class="filter-chip <?= $filter_mode==='month' ?'active':'' ?>">This Month</a>
      <span class="filter-chip <?= $filter_mode==='fysel'?'active':'' ?>" onclick="toggleExpand('fyRow')">Other FY</span>
      <span class="filter-chip <?= $filter_mode==='custom'?'active':'' ?>" onclick="toggleExpand('customRow')">Custom</span>
    </div>
  </div>
  <div class="filter-expand <?= $filter_mode==='fysel'?'show':'' ?>" id="fyRow" style="flex-direction:row;align-items:center;gap:.5rem;">
    <select id="fySelect" style="display:block;flex:1;background:var(--surface2);font-family:var(--font-body);font-size:.82rem;padding:.38rem .65rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);outline:none;color:var(--ink);appearance:none;">
      <option value="">Select FY</option>
      <?php foreach ($fy_options as $opt): ?>
        <option value="<?= htmlspecialchars($opt['value']) ?>" data-label="<?= htmlspecialchars($opt['label']) ?>"><?= htmlspecialchars($opt['label']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary btn-sm" onclick="applyFY()">Go</button>
  </div>
  <div class="filter-expand <?= $filter_mode==='custom'?'show':'' ?>" id="customRow">
    <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
      <input type="hidden" name="filter" value="custom">
      <input type="date" name="from" value="<?= $filter_mode==='custom' ? htmlspecialchars($_GET['from']??$date_from) : $fy['start'] ?>" max="<?= $today ?>">
      <span style="color:var(--subtle);font-size:.8rem;">to</span>
      <input type="date" name="to" value="<?= $filter_mode==='custom' ? htmlspecialchars($_GET['to']??$date_to) : $today ?>" max="<?= $today ?>">
      <button type="submit" class="btn btn-primary btn-sm">Apply</button>
    </form>
  </div>
</div>

<!-- SEARCH -->
<div class="search-wrap fade-up-3">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
  <input type="text" id="searchInput" placeholder="Search transactions..." oninput="filterTxns()" autocomplete="off">
  <button class="search-clear" id="searchClear" onclick="clearSearch()">x</button>
</div>

<?php if (!empty($unique_cats)): ?>
<div class="cat-chips fade-up-3" id="catChips">
  <span class="cat-chip active" data-cat="all" onclick="filterByCat(this,'all')">All</span>
  <?php foreach ($unique_cats as $uc): ?>
  <span class="cat-chip" data-cat="<?= htmlspecialchars($uc) ?>" onclick="filterByCat(this,'<?= htmlspecialchars($uc, ENT_QUOTES) ?>')"><?= htmlspecialchars($uc) ?></span>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ACTION ROW -->
<div class="action-row fade-up-4">
  <div class="action-row-left">
    <span class="txn-count" id="txnCount"><?= count($rows) ?> transactions</span>
    <button class="btn btn-outline btn-sm" onclick="openModal('categoryModal')">+ Category</button>
  </div>
  <div class="export-row">
    <a href="export.php?filter=<?= urlencode($filter_mode) ?>&from=<?= urlencode($date_from) ?>&to=<?= urlencode($date_to) ?>&type=csv" class="btn btn-outline btn-sm">CSV</a>
    <a href="export.php?filter=<?= urlencode($filter_mode) ?>&from=<?= urlencode($date_from) ?>&to=<?= urlencode($date_to) ?>&type=pdf" class="btn btn-outline btn-sm">PDF</a>
  </div>
</div>

<!-- TRANSACTIONS -->
<?php if (empty($rows)): ?>
<div class="empty-state card fade-up-4">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
  <p>No transactions for <strong><?= htmlspecialchars($filter_label) ?></strong></p>
</div>
<?php else: ?>

<!-- Desktop table -->
<div class="table-wrap desktop-only fade-up-4" id="desktopTable">
  <table class="tbl">
    <thead><tr>
      <th>Date</th><th>Description</th><th>Category</th>
      <th>Cash In</th><th>Cash Out</th><th>Balance</th><th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $row):
      $isin = floatval($row['income']) > 0;
      $bal  = floatval($row['balance']);
      $rid  = (int)($row['id'] ?? 0);
    ?>
    <tr class="tbl-row" data-desc="<?= strtolower(htmlspecialchars($row['description'])) ?>" data-cat="<?= htmlspecialchars($row['category_name']) ?>">
      <td><?= date('d M Y', strtotime($row['date'])) ?></td>
      <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($row['description']) ?></td>
      <td><span class="td-cat"><?= htmlspecialchars($row['category_name'] ?: '-') ?></span></td>
      <td class="<?= $isin ? 'td-in' : 'td-zero' ?>"><?= $isin ? '&#8377;'.number_format($row['income'],2) : '-' ?></td>
      <td class="<?= !$isin ? 'td-out' : 'td-zero' ?>"><?= !$isin ? '&#8377;'.number_format($row['expense'],2) : '-' ?></td>
      <td class="td-bal <?= $bal < 0 ? 'neg' : '' ?>">&#8377;<?= number_format($bal, 2) ?></td>
      <td>
        <div class="tbl-actions">
          <button class="tbl-action-btn edit-btn" onclick='openEdit(<?= htmlspecialchars(json_encode($row)) ?>)'>&#9998;</button>
          <button class="tbl-action-btn del-btn" onclick="confirmDel(<?= $rid ?>)">&#128465;</button>
        </div>
        <div class="confirm-del" id="del-<?= $rid ?>">
          Delete?
          <form method="POST" style="display:inline;">
            <input type="hidden" name="delete_id" value="<?= $rid ?>">
            <button type="submit" class="btn btn-red btn-sm">Yes</button>
          </form>
          <button class="btn btn-outline btn-sm" onclick="cancelDel(<?= $rid ?>)">No</button>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Mobile cards -->
<div class="txn-list mobile-only fade-up-4" id="mobileList">
  <?php foreach ($rows as $row):
    $isin = floatval($row['income']) > 0;
    $bal  = floatval($row['balance']);
    $amt  = $isin ? $row['income'] : $row['expense'];
    $rid  = (int)($row['id'] ?? 0);
  ?>
  <div class="txn-card" data-desc="<?= strtolower(htmlspecialchars($row['description'])) ?>" data-cat="<?= htmlspecialchars($row['category_name']) ?>">
    <div class="txn-dot <?= $isin ? 'in' : 'out' ?>"><?= $isin ? '+' : '-' ?></div>
    <div class="txn-info">
      <div class="txn-desc"><?= htmlspecialchars($row['description']) ?></div>
      <div class="txn-meta">
        <span class="txn-date"><?= date('d M Y', strtotime($row['date'])) ?></span>
        <?php if ($row['category_name']): ?><span class="txn-cat"><?= htmlspecialchars($row['category_name']) ?></span><?php endif; ?>
      </div>
    </div>
    <div class="txn-right">
      <span class="txn-amt <?= $isin ? 'in' : 'out' ?>"><?= $isin ? '+' : '-' ?>&#8377;<?= number_format($amt, 0) ?></span>
      <span class="txn-bal <?= $bal < 0 ? 'neg' : '' ?>">&#8377;<?= number_format($bal, 0) ?></span>
    </div>
    <div class="txn-actions">
      <button class="txn-action-btn edit-btn" onclick='openEdit(<?= htmlspecialchars(json_encode($row)) ?>)'>&#9998;</button>
      <button class="txn-action-btn del-btn" onclick="confirmDel(<?= $rid ?>)">&#128465;</button>
    </div>
  </div>
  <div class="confirm-del" id="del-mob-<?= $rid ?>">
    Delete "<strong><?= htmlspecialchars(substr($row['description'],0,20)) ?></strong>"?
    <form method="POST" style="display:inline;">
      <input type="hidden" name="delete_id" value="<?= $rid ?>">
      <button type="submit" class="btn btn-red btn-sm">Delete</button>
    </form>
    <button class="btn btn-outline btn-sm" onclick="cancelDel(<?= $rid ?>)">Cancel</button>
  </div>
  <?php endforeach; ?>
</div>

<div class="empty-state" id="noResults" style="display:none;">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
  <p>No results found</p>
</div>

<?php endif; ?>
</div>

<!-- MODAL: CASH IN -->
<div class="modal-overlay" id="incomeModal">
  <div class="modal-box">
    <span class="modal-drag"></span>
    <div class="modal-head">
      <h2 style="color:var(--green)">&#8593; Cash In</h2>
      <button class="modal-close" onclick="closeModal('incomeModal')">x</button>
    </div>
    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
      <div class="form-group"><label>Amount (Rs)</label>
        <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required inputmode="decimal"></div>
      <div class="form-group"><label>Description</label>
        <input type="text" name="description" placeholder="e.g., Monthly Salary" required></div>
      <div class="form-group"><label>Category</label>
        <select name="category_name" required>
          <option value="">-- Select --</option>
          <?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c['categories']) ?>"><?= htmlspecialchars($c['categories']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-group"><label>Date</label>
        <input type="date" name="date" value="<?= $today ?>" max="<?= $today ?>"></div>
      <button type="submit" name="savein" class="btn btn-green btn-full">Save Income</button>
    </form>
  </div>
</div>

<!-- MODAL: CASH OUT -->
<div class="modal-overlay" id="expenseModal">
  <div class="modal-box">
    <span class="modal-drag"></span>
    <div class="modal-head">
      <h2 style="color:var(--red)">&#8595; Cash Out</h2>
      <button class="modal-close" onclick="closeModal('expenseModal')">x</button>
    </div>
    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
      <div class="form-group"><label>Amount (Rs)</label>
        <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required inputmode="decimal"></div>
      <div class="form-group"><label>Description</label>
        <input type="text" name="description" placeholder="e.g., Grocery Shopping" required></div>
      <div class="form-group"><label>Category</label>
        <select name="category_name" required>
          <option value="">-- Select --</option>
          <?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c['categories']) ?>"><?= htmlspecialchars($c['categories']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-group"><label>Date</label>
        <input type="date" name="date" value="<?= $today ?>" max="<?= $today ?>"></div>
      <button type="submit" name="saveout" class="btn btn-red btn-full">Save Expense</button>
    </form>
  </div>
</div>

<!-- MODAL: EDIT -->
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <span class="modal-drag"></span>
    <div class="modal-head">
      <h2>Edit Transaction</h2>
      <button class="modal-close" onclick="closeModal('editModal')">x</button>
    </div>
    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="form-group"><label>Type</label>
        <select name="txn_type" id="edit_type">
          <option value="income">Cash In</option>
          <option value="expense">Cash Out</option>
        </select></div>
      <div class="form-group"><label>Amount (Rs)</label>
        <input type="number" name="amount" id="edit_amount" step="0.01" min="0.01" required inputmode="decimal"></div>
      <div class="form-group"><label>Description</label>
        <input type="text" name="description" id="edit_desc" required></div>
      <div class="form-group"><label>Category</label>
        <select name="category_name" id="edit_cat">
          <option value="">-- Select --</option>
          <?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c['categories']) ?>"><?= htmlspecialchars($c['categories']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-group"><label>Date</label>
        <input type="date" name="date" id="edit_date" max="<?= $today ?>"></div>
      <button type="submit" class="btn btn-primary btn-full">Save Changes</button>
    </form>
  </div>
</div>

<!-- MODAL: CATEGORY -->
<div class="modal-overlay" id="categoryModal">
  <div class="modal-box">
    <span class="modal-drag"></span>
    <div class="modal-head"><h2>Add Category</h2><button class="modal-close" onclick="closeModal('categoryModal')">x</button></div>
    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
      <div class="form-group"><label>Category Name</label>
        <input type="text" name="category" placeholder="e.g., Groceries, Rent, Fuel..." required></div>
      <button type="submit" name="savecategory" class="btn btn-primary btn-full">Add Category</button>
    </form>
  </div>
</div>

<script>
function openModal(id) {
  document.getElementById(id).classList.add('active');
  document.body.style.overflow = 'hidden';
  var inp = document.querySelector('#' + id + ' input[type=number],#' + id + ' input[type=text]');
  if (inp) setTimeout(function(){ inp.focus(); }, 300);
}
function closeModal(id) {
  document.getElementById(id).classList.remove('active');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(function(el) {
  el.addEventListener('click', function(e) { if (e.target === el) closeModal(el.id); });
});

function openEdit(r) {
  document.getElementById('edit_id').value     = r.id;
  document.getElementById('edit_amount').value = parseFloat(r.income) > 0 ? r.income : r.expense;
  document.getElementById('edit_type').value   = parseFloat(r.income) > 0 ? 'income' : 'expense';
  document.getElementById('edit_desc').value   = r.description;
  document.getElementById('edit_date').value   = r.date;
  var sel = document.getElementById('edit_cat');
  for (var i = 0; i < sel.options.length; i++) {
    if (sel.options[i].value === r.category_name) { sel.selectedIndex = i; break; }
  }
  openModal('editModal');
}

var lastDel = null;
function confirmDel(id) {
  if (lastDel !== null && lastDel !== id) {
    var old1 = document.getElementById('del-'+lastDel);
    var old2 = document.getElementById('del-mob-'+lastDel);
    if (old1) old1.classList.remove('show');
    if (old2) old2.classList.remove('show');
  }
  var el1 = document.getElementById('del-'+id);
  var el2 = document.getElementById('del-mob-'+id);
  if (el1) el1.classList.toggle('show');
  if (el2) el2.classList.toggle('show');
  lastDel = id;
}
function cancelDel(id) {
  var el1 = document.getElementById('del-'+id);
  var el2 = document.getElementById('del-mob-'+id);
  if (el1) el1.classList.remove('show');
  if (el2) el2.classList.remove('show');
  lastDel = null;
}

var activeSearch = '', activeCat = 'all';
function filterTxns() {
  activeSearch = document.getElementById('searchInput').value.toLowerCase().trim();
  document.getElementById('searchClear').classList.toggle('visible', activeSearch.length > 0);
  applyFilters();
}
function clearSearch() {
  document.getElementById('searchInput').value = '';
  activeSearch = '';
  document.getElementById('searchClear').classList.remove('visible');
  applyFilters();
}
function filterByCat(el, cat) {
  document.querySelectorAll('.cat-chip').forEach(function(c){ c.classList.remove('active'); });
  el.classList.add('active');
  activeCat = cat;
  applyFilters();
}
function applyFilters() {
  var visM = 0, visD = 0;
  document.querySelectorAll('#mobileList .txn-card').forEach(function(card) {
    var show = (!activeSearch || (card.dataset.desc||'').includes(activeSearch)) &&
               (activeCat === 'all' || card.dataset.cat === activeCat);
    card.style.display = show ? '' : 'none';
    var sib = card.nextElementSibling;
    if (sib && sib.classList.contains('confirm-del')) sib.style.display = show ? '' : 'none';
    if (show) visM++;
  });
  document.querySelectorAll('.tbl-row').forEach(function(row) {
    var show = (!activeSearch || (row.dataset.desc||'').includes(activeSearch)) &&
               (activeCat === 'all' || row.dataset.cat === activeCat);
    row.style.display = show ? '' : 'none';
    if (show) visD++;
  });
  var vis = Math.max(visM, visD);
  document.getElementById('txnCount').textContent = vis + ' transaction' + (vis !== 1 ? 's' : '');
  var noRes = document.getElementById('noResults');
  if (noRes) noRes.style.display = (vis === 0 && (activeSearch || activeCat !== 'all')) ? 'flex' : 'none';
}

function toggleExpand(id) {
  ['fyRow','customRow'].forEach(function(r) {
    if (r !== id) document.getElementById(r).classList.remove('show');
  });
  document.getElementById(id).classList.toggle('show');
}
function applyFY() {
  var sel = document.getElementById('fySelect');
  var val = sel.value;
  var label = sel.options[sel.selectedIndex] ? (sel.options[sel.selectedIndex].dataset.label || '') : '';
  if (!val) return;
  window.location.href = '?filter=fysel&fyval=' + encodeURIComponent(val) + '&fylabel=' + encodeURIComponent(label);
}

var darkBtn = document.getElementById('darkToggle');
function applyTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
  darkBtn.textContent = t === 'dark' ? '☀️' : '🌙';
  localStorage.setItem('et_theme', t);
}
applyTheme(localStorage.getItem('et_theme') || 'light');
darkBtn.addEventListener('click', function() {
  applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
});

setTimeout(function() {
  document.querySelectorAll('.alert').forEach(function(el) {
    el.style.transition = 'opacity .4s'; el.style.opacity = '0';
    setTimeout(function(){ el.remove(); }, 400);
  });
}, 3000);
</script>
</body>
</html>
