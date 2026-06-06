<?php
session_name("ExpenseTracker"); session_start();
include "db.php";
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { header("location: login.php"); exit; }
$id = $_SESSION['user_id'];

// ADD BOOK
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Save'])) {
    $book_name = trim($_POST["bookname"]);
    if (!empty($book_name)) {
        $mx = $conn->prepare("SELECT COALESCE(MAX(sort_order),0)+1 AS next FROM books WHERE user_id=?");
        $mx->bind_param("i",$id); $mx->execute();
        $next = $mx->get_result()->fetch_assoc()['next'] ?? 0;

        $col_check = $conn->query("SHOW COLUMNS FROM books LIKE 'sort_order'");
        if ($col_check && $col_check->num_rows === 0) {
            $conn->query("ALTER TABLE books ADD COLUMN sort_order INT DEFAULT 0");
        }
        $s = $conn->prepare("INSERT INTO `books`(`book_name`,`user_id`,`sort_order`) VALUES(?,?,?)");
        $s->bind_param("sii",$book_name,$id,$next);
        $executed = $s->execute();
        if ($executed) { $_SESSION['success'] = "Book \"".htmlspecialchars($book_name)."\" created!"; }
        else { $_SESSION['error'] = "Error creating book."; }
    }
    header("Location: books.php"); exit;
}

// SELECT BOOK
if (isset($_POST['book_btn'])) {
    [$bookName,$book_id] = explode('|',$_POST['book_btn']);
    $_SESSION['bookname'] = $bookName;
    $_SESSION['book_id']  = $book_id;
    header("Location: dashboard.php"); exit;
}

// RENAME BOOK
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rename_book_id'])) {
    $rename_id   = (int)$_POST['rename_book_id'];
    $rename_name = trim($_POST['rename_book_name'] ?? '');
    if ($rename_id > 0 && !empty($rename_name)) {
        $r = $conn->prepare("UPDATE books SET book_name=? WHERE book_id=? AND user_id=?");
        $r->bind_param("sii", $rename_name, $rename_id, $id);
        if ($r->execute() && $r->affected_rows >= 0) {
            if (isset($_SESSION['book_id']) && $_SESSION['book_id'] == $rename_id) {
                $_SESSION['bookname'] = $rename_name;
            }
            $_SESSION['success'] = "Book renamed to \"" . htmlspecialchars($rename_name) . "\".";
        } else {
            $_SESSION['error'] = "Could not rename book.";
        }
    } else {
        $_SESSION['error'] = "Book name cannot be empty.";
    }
    header("Location: books.php"); exit;
}

// Ensure sort_order column exists
$col_check = $conn->query("SHOW COLUMNS FROM books LIKE 'sort_order'");
if ($col_check && $col_check->num_rows === 0) {
    $conn->query("ALTER TABLE books ADD COLUMN sort_order INT DEFAULT 0");
}

// FETCH BOOKS
$s = $conn->prepare("SELECT * FROM books WHERE user_id=? ORDER BY sort_order ASC, book_id ASC");
if (!$s) $s = $conn->prepare("SELECT * FROM books WHERE user_id=? ORDER BY sort_order ASC, id ASC");
$s->bind_param("i",$id); $s->execute();
$books = $s->get_result()->fetch_all(MYSQLI_ASSOC);

// FETCH TOTALS
$book_totals = [];
foreach ($books as $b) {
    $bid = $b['book_id'] ?? $b['id'] ?? 0;
    $q = $conn->prepare("SELECT SUM(income) ti, SUM(expense) te FROM expenses WHERE user_id=? AND book_id=?");
    $q->bind_param("ii",$id,$bid); $q->execute();
    $r = $q->get_result()->fetch_assoc();
    $book_totals[$bid] = ['income'=>$r['ti']??0,'expense'=>$r['te']??0,'balance'=>($r['ti']??0)-($r['te']??0)];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<?php include '_style.php'; ?>
<title>My Books – ExpenseTracker</title>
<style>
/* ── PAGE HEADER ── */
.page-hd{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-bottom:0;}
.page-hd h1{font-family:var(--font-head);font-size:1.2rem;font-weight:700;}
.page-hd-right{display:flex;gap:.4rem;align-items:center;}

/* ── ADD BOOK FORM ── */
.add-book-form{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius);padding:1.1rem;display:none;margin-top:.75rem;animation:fadeUp .25s ease both;}
.add-book-form.open{display:block;}
.add-book-row{display:flex;gap:.5rem;align-items:flex-end;}
.add-book-row .form-group{flex:1;margin:0;}

/* ── BOOKS LIST ── */
.books-list{display:flex;flex-direction:column;gap:.6rem;margin-top:.85rem;}

/* ── BOOK CARD ── */
.book-card{
    background:var(--surface);
    border:1.5px solid var(--border-soft);
    border-radius:var(--radius);
    overflow:hidden;
    position:relative;
    transition:box-shadow .2s, border-color .2s, opacity .2s;
    display:flex;
    align-items:stretch;
    user-select:none;
}
.book-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--brand),#818cf8);z-index:1;}

/* Drag states */
.book-card.dragging{opacity:.45;box-shadow:var(--shadow-lg);border-color:var(--brand);}
.book-card.drag-over{border-color:var(--brand);box-shadow:0 0 0 2px rgba(37,99,235,.25);}
.books-list.drag-active .book-card:not(.dragging){transform:none;}

/* ── DRAG HANDLE ── */
.drag-handle{
    display:flex;
    align-items:center;
    justify-content:center;
    padding:.5rem .55rem;
    color:var(--subtle);
    cursor:grab;
    flex-shrink:0;
    touch-action:none;
    font-size:1rem;
    transition:color .15s;
    border-right:1px solid var(--border-soft);
}
.drag-handle:active{cursor:grabbing;color:var(--brand);}
.drag-handle:hover{color:var(--brand);}

/* ── BOOK MAIN BUTTON ── */
.book-btn-form{flex:1;min-width:0;display:flex;flex-direction:column;}
.book-btn-form > form > button{
    background:none;border:none;cursor:pointer;
    width:100%;text-align:left;padding:.85rem .9rem;
    display:block;transition:background .15s;
}
.book-btn-form > form > button:hover{background:var(--surface2);}

.book-name{font-family:var(--font-head);font-size:.95rem;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:.4rem;margin-bottom:.65rem;}
.book-name svg{width:15px;height:15px;color:var(--brand);flex-shrink:0;}

.book-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.35rem;}
.bstat{background:var(--surface2);border-radius:var(--radius-sm);padding:.4rem .5rem;}
.bstat-label{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:var(--subtle);margin-bottom:.1rem;}
.bstat-val{font-family:var(--font-head);font-size:.83rem;font-weight:700;}
.bstat-val.blue{color:var(--brand);}
.bstat-val.green{color:var(--green);}
.bstat-val.red{color:var(--red);}

/* ── RENAME INLINE ── */
.rename-btn{
    width:30px;height:30px;border-radius:var(--radius-sm);
    border:1px solid var(--border);background:var(--surface2);
    color:var(--muted);display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:.8rem;transition:var(--transition);flex-shrink:0;
    padding:0;
}
.rename-btn:hover{background:var(--brand-light);border-color:var(--brand);color:var(--brand);}

.rename-inline{
    display:none;
    padding:.6rem .9rem .75rem;
    border-top:1px solid var(--border-soft);
    background:var(--surface2);
    animation:fadeUp .2s ease both;
}
.rename-inline.open{display:block;}
.rename-row{display:flex;gap:.4rem;align-items:center;}
.rename-row input{
    flex:1;padding:.42rem .6rem;border-radius:var(--radius-sm);
    border:1.5px solid var(--border);background:var(--surface);
    color:var(--ink);font-size:.85rem;
    transition:border-color .15s;
}
.rename-row input:focus{outline:none;border-color:var(--brand);}
.rename-row .btn{padding:.38rem .7rem;font-size:.78rem;}

/* ── ARROW + ACTION BUTTONS ── */
.book-arrows{
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    gap:.25rem;
    padding:.5rem .45rem;
    border-left:1px solid var(--border-soft);
    flex-shrink:0;
}
.arrow-btn{
    width:26px;height:26px;
    border-radius:var(--radius-sm);
    border:1px solid var(--border);
    background:var(--surface2);
    color:var(--muted);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:.7rem;
    transition:var(--transition);
    line-height:1;
    padding:0;
}
.arrow-btn:hover:not(:disabled){background:var(--brand-light);border-color:var(--brand);color:var(--brand);}
.arrow-btn:disabled{opacity:.25;cursor:default;}

/* ── REORDER HINT ── */
.reorder-hint{
    display:flex;align-items:center;gap:.4rem;
    font-size:.73rem;color:var(--subtle);
    margin-top:.5rem;
    padding:.4rem .6rem;
    background:var(--surface2);
    border-radius:var(--radius-sm);
    border:1px dashed var(--border);
}

/* ── SAVE ORDER TOAST ── */
.order-toast{
    position:fixed;bottom:1.25rem;left:50%;transform:translateX(-50%) translateY(60px);
    background:var(--ink);color:#fff;
    font-size:.8rem;font-weight:700;
    padding:.55rem 1.1rem;
    border-radius:99px;
    z-index:9999;
    opacity:0;
    transition:opacity .25s, transform .25s;
    pointer-events:none;
    white-space:nowrap;
}
.order-toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
.order-toast.saved{background:var(--green);}
.order-toast.error{background:var(--red);}

/* Desktop: switch to grid layout */
@media(min-width:700px){
    .books-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));}
    .book-arrows{flex-direction:column;}
}
</style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
    ExpenseTracker
  </a>
  <div class="navbar-spacer"></div>
  <ul class="navbar-menu" style="display:flex; list-style:none; margin:0; padding:0; gap:.5rem; align-items:center;">
    <li><a href="profile.php" class="nav-btn" style="color:var(--ink); font-weight:600; text-decoration:none; font-size:.9rem; padding:.5rem .8rem; border-radius:var(--radius-sm); transition:var(--transition);">Profile</a></li>
    <li><a href="logout.php" class="btn btn-outline btn-sm nav-logout">Sign Out</a></li>
  </ul>
  <button class="dark-toggle" id="darkToggle" title="Toggle dark mode">🌙</button>
</nav>

<div class="page-wrap">

  <div class="page-hd fade-up">
    <h1>📒 My Books</h1>
    <div class="page-hd-right">
      <button class="btn btn-primary btn-sm" onclick="toggleAdd()">+ New Book</button>
    </div>
  </div>

  <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success fade-up" style="margin-top:.75rem;">✓ <?=htmlspecialchars($_SESSION['success'])?></div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-error fade-up" style="margin-top:.75rem;">✗ <?=htmlspecialchars($_SESSION['error'])?></div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Add Book Form -->
  <div class="add-book-form" id="addForm">
    <form method="POST">
      <div class="add-book-row">
        <div class="form-group">
          <label>Book Name</label>
          <input type="text" name="bookname" placeholder="e.g., Personal, Business, Home…" required id="bookNameInput">
        </div>
        <button type="submit" name="Save" class="btn btn-primary">Create</button>
        <button type="button" class="btn btn-outline" onclick="toggleAdd()">✕</button>
      </div>
    </form>
  </div>

  <?php if(empty($books)): ?>
  <div class="empty-state fade-up-2" style="margin-top:2rem;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border-soft);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    <p>No books yet.<br>Create your first book to start tracking.</p>
    <button class="btn btn-primary btn-sm" onclick="toggleAdd()">+ Create Book</button>
  </div>

  <?php else: ?>

  <?php if(count($books) > 1): ?>
  <div class="reorder-hint fade-up-2">
    <span>☰</span> Drag to reorder · or use ↑↓ arrows · ✏️ to rename
  </div>
  <?php endif; ?>

  <!-- Books list -->
  <div class="books-list fade-up-2" id="booksList">
    <?php foreach($books as $idx => $b):
      $bid     = $b['book_id'] ?? $b['id'] ?? 0;
      $t       = $book_totals[$bid];
      $balCls  = $t['balance'] >= 0 ? 'blue' : 'red';
      $isFirst = $idx === 0;
      $isLast  = $idx === count($books) - 1;
    ?>
    <div class="book-card" data-id="<?=$bid?>" draggable="true">

      <!-- Drag handle -->
      <div class="drag-handle" title="Drag to reorder">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
          <circle cx="9" cy="5"  r="1.5"/><circle cx="15" cy="5"  r="1.5"/>
          <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
          <circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/>
        </svg>
      </div>

      <!-- Book select button + rename panel -->
      <div class="book-btn-form">
        <form method="POST">
          <input type="hidden" name="book_btn" value="<?=htmlspecialchars($b['book_name'].'|'.$bid)?>">
          <button type="submit">
            <div class="book-name">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
              <?=htmlspecialchars($b['book_name'])?>
            </div>
            <div class="book-stats">
              <div class="bstat"><div class="bstat-label">Balance</div><div class="bstat-val <?=$balCls?>">₹<?=number_format(abs($t['balance']),0)?></div></div>
              <div class="bstat"><div class="bstat-label">In</div><div class="bstat-val green">₹<?=number_format($t['income'],0)?></div></div>
              <div class="bstat"><div class="bstat-label">Out</div><div class="bstat-val red">₹<?=number_format($t['expense'],0)?></div></div>
            </div>
          </button>
        </form>

        <!-- Rename inline panel -->
        <div class="rename-inline" id="rename-<?=$bid?>">
          <form method="POST">
            <input type="hidden" name="rename_book_id" value="<?=$bid?>">
            <div class="rename-row">
              <input type="text"
                     name="rename_book_name"
                     value="<?=htmlspecialchars($b['book_name'])?>"
                     placeholder="New book name…"
                     required
                     id="rename-input-<?=$bid?>">
              <button type="submit" class="btn btn-primary">Save</button>
              <button type="button" class="btn btn-outline" onclick="closeRename(<?=$bid?>)">✕</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Rename + Up/Down buttons -->
      <div class="book-arrows">
        <button class="rename-btn" onclick="toggleRename(<?=$bid?>)" title="Rename book">✏️</button>
        <button class="arrow-btn" onclick="moveCard(this,-1)" title="Move up"  <?=$isFirst?'disabled':''?>>▲</button>
        <button class="arrow-btn" onclick="moveCard(this,+1)" title="Move down" <?=$isLast ?'disabled':''?>>▼</button>
      </div>

    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>
</div>

<!-- Toast -->
<div class="order-toast" id="toast"></div>

<script>
// ── Dark mode ─────────────────────────────────────────────────────────
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

// ── Alert dismiss ──────────────────────────────────────────────────────
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(el => {
    el.style.transition = 'opacity .4s'; el.style.opacity = '0';
    setTimeout(() => el.remove(), 400);
  });
}, 3000);

// ── Add book toggle ────────────────────────────────────────────────────
function toggleAdd() {
  const f = document.getElementById('addForm');
  f.classList.toggle('open');
  if (f.classList.contains('open')) setTimeout(() => document.getElementById('bookNameInput').focus(), 50);
}

// ── Toast helper ───────────────────────────────────────────────────────
let toastTimer;
function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'order-toast show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2200);
}

// ── Save order to server ───────────────────────────────────────────────
function saveOrder() {
  const cards = document.querySelectorAll('#booksList .book-card');
  const order = [...cards].map(c => parseInt(c.dataset.id));
  showToast('Saving…');
  fetch('reorder.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ order })
  })
  .then(r => r.json())
  .then(d => showToast(d.ok ? '✓ Order saved' : '✗ ' + d.msg, d.ok ? 'saved' : 'error'))
  .catch(() => showToast('✗ Network error', 'error'));
}

// ── Arrow button reorder ───────────────────────────────────────────────
function moveCard(btn, dir) {
  const card = btn.closest('.book-card');
  const list = document.getElementById('booksList');
  const cards = [...list.querySelectorAll('.book-card')];
  const idx = cards.indexOf(card);
  const target = cards[idx + dir];
  if (!target) return;
  if (dir === -1) list.insertBefore(card, target);
  else            list.insertBefore(target, card);
  refreshArrows();
  saveOrder();
}

function refreshArrows() {
  const cards = [...document.querySelectorAll('#booksList .book-card')];
  cards.forEach((c, i) => {
    const btns = c.querySelectorAll('.arrow-btn');
    btns[0].disabled = i === 0;
    btns[1].disabled = i === cards.length - 1;
  });
}

// ── Rename inline toggle ───────────────────────────────────────────────
function toggleRename(bid) {
  const panel  = document.getElementById('rename-' + bid);
  const isOpen = panel.classList.contains('open');
  // Close all open rename panels first
  document.querySelectorAll('.rename-inline.open').forEach(p => p.classList.remove('open'));
  if (!isOpen) {
    panel.classList.add('open');
    const input = document.getElementById('rename-input-' + bid);
    setTimeout(() => { input.focus(); input.select(); }, 50);
  }
}

function closeRename(bid) {
  document.getElementById('rename-' + bid).classList.remove('open');
}

// ── Drag & Drop (desktop mouse) ────────────────────────────────────────
let dragSrc = null;

document.querySelectorAll('.book-card').forEach(card => {
  card.addEventListener('dragstart', e => {
    dragSrc = card;
    card.classList.add('dragging');
    document.getElementById('booksList').classList.add('drag-active');
    e.dataTransfer.effectAllowed = 'move';
  });

  card.addEventListener('dragend', () => {
    card.classList.remove('dragging');
    document.getElementById('booksList').classList.remove('drag-active');
    document.querySelectorAll('.book-card').forEach(c => c.classList.remove('drag-over'));
    refreshArrows();
    saveOrder();
  });

  card.addEventListener('dragover', e => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (card !== dragSrc) {
      document.querySelectorAll('.book-card').forEach(c => c.classList.remove('drag-over'));
      card.classList.add('drag-over');
    }
  });

  card.addEventListener('drop', e => {
    e.preventDefault();
    if (dragSrc && dragSrc !== card) {
      const list   = document.getElementById('booksList');
      const cards  = [...list.querySelectorAll('.book-card')];
      const srcIdx = cards.indexOf(dragSrc);
      const dstIdx = cards.indexOf(card);
      if (srcIdx < dstIdx) list.insertBefore(dragSrc, card.nextSibling);
      else                  list.insertBefore(dragSrc, card);
    }
    card.classList.remove('drag-over');
  });
});

// ── Touch drag (mobile) ────────────────────────────────────────────────
(function () {
  const list = document.getElementById('booksList');
  if (!list) return;

  let touchCard = null, clone = null, startY = 0, offsetY = 0;

  list.querySelectorAll('.drag-handle').forEach(handle => {
    handle.addEventListener('touchstart', e => {
      touchCard = handle.closest('.book-card');
      const touch = e.touches[0];
      const rect  = touchCard.getBoundingClientRect();

      startY  = touch.clientY;
      offsetY = touch.clientY - rect.top;

      clone = touchCard.cloneNode(true);
      clone.style.cssText = `
        position:fixed;left:${rect.left}px;top:${rect.top}px;
        width:${rect.width}px;z-index:9999;opacity:.85;pointer-events:none;
        box-shadow:0 8px 30px rgba(0,0,0,.2);border-radius:14px;
        transition:none;
      `;
      document.body.appendChild(clone);
      touchCard.style.opacity = '.3';
      e.preventDefault();
    }, { passive: false });
  });

  document.addEventListener('touchmove', e => {
    if (!touchCard || !clone) return;
    e.preventDefault();

    const touch = e.touches[0];
    const y = touch.clientY - offsetY;
    clone.style.top = y + 'px';

    clone.style.display = 'none';
    const el = document.elementFromPoint(touch.clientX, touch.clientY);
    clone.style.display = '';
    const over = el?.closest('.book-card');

    if (over && over !== touchCard) {
      const cards  = [...list.querySelectorAll('.book-card')];
      const srcIdx = cards.indexOf(touchCard);
      const dstIdx = cards.indexOf(over);
      if (srcIdx < dstIdx) list.insertBefore(touchCard, over.nextSibling);
      else                  list.insertBefore(touchCard, over);
    }
  }, { passive: false });

  document.addEventListener('touchend', () => {
    if (!touchCard) return;
    touchCard.style.opacity = '';
    if (clone) { clone.remove(); clone = null; }
    refreshArrows();
    saveOrder();
    touchCard = null;
  });
})();
</script>
</body>
</html>