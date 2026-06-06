<?php
session_name("ExpenseTracker"); session_start();
include "db.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { header("location: login.php"); exit; }
if (!isset($_SESSION['book_id'])) { header("location: books.php"); exit; }

$user_id  = $_SESSION['user_id'];
$book_id  = $_SESSION['book_id'];
$bookName = $_SESSION['bookname'] ?? 'Book';
$type     = $_GET['type'] ?? 'csv';

// ── Date range from GET (same as dashboard) ──────────────────────────
function getCurrentFY() {
    $m=(int)date('n'); $y=(int)date('Y');
    return $m>=4 ? ['start'=>"$y-04-01",'end'=>($y+1)."-03-31",'label'=>"FY $y–".($y+1)]
                 : ['start'=>($y-1)."-04-01",'end'=>"$y-03-31",'label'=>"FY ".($y-1)."–$y"];
}
$fy = getCurrentFY();
$filter_mode = $_GET['filter'] ?? 'fy';
$date_from   = $fy['start']; $date_to = $fy['end']; $filter_label = $fy['label'];
if ($filter_mode==='custom')  { $date_from=preg_replace('/[^0-9\-]//','',$_GET['from']??$fy['start']); $date_to=preg_replace('/[^0-9\-]//','',$_GET['to']??$fy['end']); $filter_label=date('d M Y',strtotime($date_from)).' to '.date('d M Y',strtotime($date_to)); }
elseif($filter_mode==='all')  { $date_from='2000-01-01'; $date_to='2099-12-31'; $filter_label='All Time'; }
elseif($filter_mode==='month'){ $date_from=date('Y-m-01'); $date_to=date('Y-m-t'); $filter_label=date('F Y'); }
elseif($filter_mode==='fysel'){ $fyval=$_GET['fyval']??''; if($fyval&&strpos($fyval,'|')!==false){[$date_from,$date_to]=explode('|',$fyval);$date_from=preg_replace('/[^0-9\-]//','',$date_from);$date_to=preg_replace('/[^0-9\-]//','',$date_to);} $filter_label=$_GET['fylabel']??'Selected FY'; }

// Fetch rows
$st=$conn->prepare("SELECT date,description,category_name,income,expense,balance FROM expenses WHERE user_id=? AND book_id=? AND date BETWEEN ? AND ? ORDER BY date ASC,id ASC");
$st->bind_param("iiss",$user_id,$book_id,$date_from,$date_to); $st->execute();
$rows=$st->get_result()->fetch_all(MYSQLI_ASSOC);

// Summary
$total_income  = array_sum(array_column($rows,'income'));
$total_expense = array_sum(array_column($rows,'expense'));
$net = $total_income - $total_expense;

$filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $bookName) . '_' . $filter_label;

// ── CSV EXPORT ───────────────────────────────────────────────────────
if ($type === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Pragma: no-cache');

    $out = fopen('php://output','w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

    fputcsv($out, ['ExpenseTracker — ' . $bookName]);
    fputcsv($out, ['Period: ' . $filter_label]);
    fputcsv($out, ['Exported: ' . date('d M Y H:i')]);
    fputcsv($out, []);
    fputcsv($out, ['Date','Description','Category','Cash In (₹)','Cash Out (₹)','Balance (₹)']);

    foreach ($rows as $r) {
        fputcsv($out, [
            date('d-M-Y', strtotime($r['date'])),
            $r['description'],
            $r['category_name'],
            $r['income']  > 0 ? number_format($r['income'],  2, '.', '') : '',
            $r['expense'] > 0 ? number_format($r['expense'], 2, '.', '') : '',
            number_format($r['balance'], 2, '.', ''),
        ]);
    }
    fputcsv($out, []);
    fputcsv($out, ['', '', 'TOTAL', number_format($total_income,2,'.',''). '', number_format($total_expense,2,'.',''). '', number_format($net,2,'.',''). '']);
    fclose($out);
    exit;
}

// ── PDF EXPORT (pure PHP, no libs needed — HTML rendered as PDF via browser) ─
if ($type === 'pdf') {
    // We generate a clean print-optimized HTML page and trigger print dialog
    // This works on all devices without any server-side PDF library
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($bookName)?> – <?=htmlspecialchars($filter_label)?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;font-size:12px;color:#111;background:#fff;padding:24px;}
.header{margin-bottom:20px;border-bottom:2px solid #2563eb;padding-bottom:12px;}
.header h1{font-size:18px;font-weight:700;color:#2563eb;margin-bottom:4px;}
.header p{font-size:11px;color:#6b7280;}
.summary{display:flex;gap:16px;margin-bottom:20px;}
.sum-box{flex:1;padding:10px 14px;border-radius:8px;text-align:center;}
.sum-box.bal{background:#eff6ff;color:#2563eb;}
.sum-box.inc{background:#f0fdf4;color:#16a34a;}
.sum-box.exp{background:#fef2f2;color:#dc2626;}
.sum-box .label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;}
.sum-box .val{font-size:15px;font-weight:800;}
table{width:100%;border-collapse:collapse;font-size:11px;}
th{background:#f3f4f8;padding:7px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.3px;color:#6b7280;border-bottom:1px solid #e5e7eb;}
td{padding:7px 10px;border-bottom:1px solid #f1f1f4;vertical-align:middle;}
tr:hover td{background:#f9fafb;}
.cat-badge{font-size:9px;background:#f3f4f8;padding:2px 6px;border-radius:99px;color:#6b7280;font-weight:700;}
.td-in{color:#16a34a;font-weight:700;}
.td-out{color:#dc2626;font-weight:700;}
.td-bal{font-weight:700;color:#2563eb;}
.td-bal.neg{color:#dc2626;}
.td-zero{color:#d1d5db;}
tfoot td{font-weight:700;background:#f3f4f8;border-top:2px solid #e5e7eb;}
.footer{margin-top:20px;text-align:center;font-size:9px;color:#9ca3af;}
@media print{
  body{padding:0;}
  .no-print{display:none!important;}
  @page{margin:1.5cm;}
}
</style>
</head>
<body>

<div class="no-print" style="background:#2563eb;color:#fff;padding:10px 16px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
  <span style="font-size:13px;font-weight:700;">📄 Ready to save as PDF</span>
  <div style="display:flex;gap:8px;">
    <button onclick="window.print()" style="background:#fff;color:#2563eb;border:none;border-radius:6px;padding:6px 16px;font-weight:700;cursor:pointer;font-size:12px;">Save as PDF / Print</button>
    <a href="javascript:history.back()" style="background:rgba(255,255,255,.15);color:#fff;border:none;border-radius:6px;padding:6px 14px;font-weight:700;cursor:pointer;font-size:12px;text-decoration:none;">← Back</a>
  </div>
</div>

<div class="header">
  <h1>📒 <?=htmlspecialchars($bookName)?> — Statement</h1>
  <p>Period: <?=htmlspecialchars($filter_label)?> &nbsp;·&nbsp; Exported: <?=date('d M Y H:i')?> &nbsp;·&nbsp; <?=count($rows)?> transactions</p>
</div>

<div class="summary">
  <div class="sum-box bal"><div class="label">Net Balance</div><div class="val">₹<?=number_format($net,2)?></div></div>
  <div class="sum-box inc"><div class="label">Total Income</div><div class="val">₹<?=number_format($total_income,2)?></div></div>
  <div class="sum-box exp"><div class="label">Total Expense</div><div class="val">₹<?=number_format($total_expense,2)?></div></div>
</div>

<?php if(empty($rows)): ?>
<p style="text-align:center;color:#6b7280;padding:2rem;">No transactions for this period.</p>
<?php else: ?>
<table>
  <thead><tr>
    <th>#</th><th>Date</th><th>Description</th><th>Category</th>
    <th>Cash In</th><th>Cash Out</th><th>Balance</th>
  </tr></thead>
  <tbody>
  <?php $n=1; foreach($rows as $r):
    $isin=floatval($r['income'])>0; $bal=floatval($r['balance']);
  ?>
  <tr>
    <td style="color:#9ca3af"><?=$n++?></td>
    <td><?=date('d M Y',strtotime($r['date']))?></td>
    <td><?=htmlspecialchars($r['description'])?></td>
    <td><?php if($r['category_name']): ?><span class="cat-badge"><?=htmlspecialchars($r['category_name'])?></span><?php else: ?>—<?php endif; ?></td>
    <td class="<?=$isin?'td-in':'td-zero'?>"><?=$isin?'₹'.number_format($r['income'],2):'—'?></td>
    <td class="<?=!$isin?'td-out':'td-zero'?>"><?=!$isin?'₹'.number_format($r['expense'],2):'—'?></td>
    <td class="td-bal <?=$bal<0?'neg':''?>">₹<?=number_format($bal,2)?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot><tr>
    <td colspan="4" style="text-align:right;">TOTALS</td>
    <td class="td-in">₹<?=number_format($total_income,2)?></td>
    <td class="td-out">₹<?=number_format($total_expense,2)?></td>
    <td class="td-bal <?=$net<0?'neg':''?>">₹<?=number_format($net,2)?></td>
  </tr></tfoot>
</table>
<?php endif; ?>

<div class="footer">ExpenseTracker · <?=htmlspecialchars($bookName)?> · Generated <?=date('d M Y H:i')?></div>

<script>
// Auto-trigger print dialog after short delay
setTimeout(()=>window.print(), 600);
</script>
</body>
</html>
<?php
    exit;
}

// fallback
header("Location: dashboard.php");
exit;
?>
