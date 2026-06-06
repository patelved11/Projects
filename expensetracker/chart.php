<?php
session_name("ExpenseTracker"); session_start();
include "db.php";
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { header("location: login.php"); exit; }
if (!isset($_SESSION['book_id'])) { header("location: books.php"); exit; }
$user_id=$_SESSION['user_id']; $book_id=$_SESSION['book_id']; $bookName=$_SESSION['bookname']??'My Book'; $today=date("Y-m-d");

function getCurrentFY(){$m=(int)date('n');$y=(int)date('Y');return $m>=4?['start'=>"$y-04-01",'end'=>($y+1)."-03-31",'label'=>'FY '.$y.'–'.($y+1)]:['start'=>($y-1)."-04-01",'end'=>"$y-03-31",'label'=>'FY '.($y-1).'–'.$y];}
function getFYOptions(){$m=(int)date('n');$y=(int)date('Y');$csy=($m>=4)?$y:$y-1;$opts=[];for($i=0;$i<=4;$i++){$sy=$csy-$i;$ey=$sy+1;$opts[]=["start"=>"$sy-04-01","end"=>"$ey-03-31","label"=>'FY '.$sy.'–'.$ey,"value"=>"$sy-04-01|$ey-03-31"];}return $opts;}

// ✅ FIX: Safe date parser — handles slashes, validates, rejects epoch
function safeDate($str) {
    if (!$str) return false;
    $str = str_replace('/', '-', trim($str));
    $str = preg_replace('/[^0-9\-]/', '', $str);
    $ts = strtotime($str);
    if (!$ts || $ts <= 0) return false;
    return date('Y-m-d', $ts);
}

$fy=getCurrentFY();
$filter_mode=$_GET['filter']??'fy';
$date_from=$fy['start'];
$date_to=$fy['end'];
$filter_label=$fy['label'];

// ✅ FIX: Custom date block now uses safeDate() with fallback
if($filter_mode==='custom'){
    $parsed_from = safeDate($_GET['from'] ?? '');
    $parsed_to   = safeDate($_GET['to']   ?? '');
    if($parsed_from && $parsed_to){
        $date_from    = $parsed_from;
        $date_to      = $parsed_to;
        $filter_label = date('d M Y', strtotime($date_from)).' – '.date('d M Y', strtotime($date_to));
    } else {
        $date_from    = $fy['start'];
        $date_to      = $fy['end'];
        $filter_label = $fy['label'];
        $filter_mode  = 'fy';
    }
}
elseif($filter_mode==='all'){$date_from='2000-01-01';$date_to='2099-12-31';$filter_label='All Time';}
elseif($filter_mode==='month'){$date_from=date('Y-m-01');$date_to=date('Y-m-t');$filter_label=date('F Y');}
elseif($filter_mode==='fysel'){
    $fyval=$_GET['fyval']??'';
    if($fyval&&strpos($fyval,'|')!==false){
        [$f,$t]=explode('|',$fyval);
        $pf=safeDate($f); $pt=safeDate($t);
        if($pf&&$pt){$date_from=$pf;$date_to=$pt;}
    }
    $filter_label=$_GET['fylabel']??'Selected FY';
}

// Category donut
$sql="SELECT c.categories,SUM(e.expense) AS total_expense FROM categories c LEFT JOIN expenses e ON c.categories=e.category_name AND e.user_id=? AND e.book_id=? AND e.date BETWEEN ? AND ? WHERE c.users_id IN(1,?) GROUP BY c.categories HAVING total_expense>0 ORDER BY total_expense DESC";
$st=$conn->prepare($sql);$st->bind_param("iissi",$user_id,$book_id,$date_from,$date_to,$user_id);$st->execute();$res=$st->get_result();
$data_source=[];while($r=$res->fetch_assoc())$data_source[$r['categories']]=round($r['total_expense']);

// Monthly bar
$sql2="SELECT DATE_FORMAT(date,'%Y-%m') as month,SUM(income) as ti,SUM(expense) as te FROM expenses WHERE user_id=? AND book_id=? AND date BETWEEN ? AND ? GROUP BY month ORDER BY month ASC";
$st2=$conn->prepare($sql2);$st2->bind_param("iiss",$user_id,$book_id,$date_from,$date_to);$st2->execute();$monthly=$st2->get_result()->fetch_all(MYSQLI_ASSOC);
$month_labels=array_map(fn($r)=>date('M y',strtotime($r['month'].'-01')),$monthly);
$month_inc=array_map(fn($r)=>round($r['ti']),$monthly);
$month_exp=array_map(fn($r)=>round($r['te']),$monthly);

// 6-month trend
$t6start=date('Y-m-01',strtotime('-5 months')); $t6end=date('Y-m-t');
$st3=$conn->prepare("SELECT DATE_FORMAT(date,'%Y-%m') ym,SUM(income) ti,SUM(expense) te FROM expenses WHERE user_id=? AND book_id=? AND date BETWEEN ? AND ? GROUP BY ym ORDER BY ym ASC");
$st3->bind_param("iiss",$user_id,$book_id,$t6start,$t6end);$st3->execute();
$tm_rows=$st3->get_result()->fetch_all(MYSQLI_ASSOC);
$tm_map=[];foreach($tm_rows as $r)$tm_map[$r['ym']]=$r;
$trend_labels=[];$trend_inc=[];$trend_exp=[];
for($i=5;$i>=0;$i--){$ym=date('Y-m',strtotime("-$i months"));$trend_labels[]=date('M',strtotime("-$i months"));$trend_inc[]=round(floatval($tm_map[$ym]['ti']??0));$trend_exp[]=round(floatval($tm_map[$ym]['te']??0));}

// Category breakdown
$st4=$conn->prepare("SELECT category_name,SUM(expense) AS total FROM expenses WHERE user_id=? AND book_id=? AND date BETWEEN ? AND ? AND expense>0 GROUP BY category_name ORDER BY total DESC LIMIT 8");
$st4->bind_param("iiss",$user_id,$book_id,$date_from,$date_to);$st4->execute();
$cat_breakdown=$st4->get_result()->fetch_all(MYSQLI_ASSOC);
$totalCatExp=array_sum(array_column($cat_breakdown,'total'));

$base_colors=['#2563eb','#16a34a','#d97706','#dc2626','#7c3aed','#0891b2','#db2777','#65a30d','#ea580c','#0284c7','#9333ea','#e11d48'];
$bg_cols=[];for($i=0;$i<count($data_source);$i++)$bg_cols[]=$base_colors[$i%count($base_colors)];
$total_exp=array_sum($data_source);
$fy_options=getFYOptions();
$qs=http_build_query(array_filter(['filter'=>$filter_mode,'from'=>$_GET['from']??'','to'=>$_GET['to']??'','fyval'=>$_GET['fyval']??'','fylabel'=>$_GET['fylabel']??'']));
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<?php include '_style.php'; ?>
<title>Charts – <?=htmlspecialchars($bookName)?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
.charts-grid{display:grid;grid-template-columns:1fr;gap:.85rem;margin-top:.75rem;}
@media(min-width:860px){.charts-grid{grid-template-columns:1fr 1fr;}}
.chart-card{background:var(--surface);border:1px solid var(--border-soft);border-radius:var(--radius);padding:1.1rem;}
.chart-card h3{font-family:var(--font-head);font-size:.92rem;font-weight:700;color:var(--ink);margin-bottom:.75rem;display:flex;align-items:center;justify-content:space-between;}
.total-pill{font-size:.72rem;font-weight:700;background:var(--red-bg);color:var(--red);padding:.2rem .6rem;border-radius:99px;}
.legend-grid{display:flex;flex-wrap:wrap;gap:.4rem .75rem;margin-top:.85rem;}
.leg-item{display:flex;align-items:center;gap:.3rem;font-size:.73rem;font-weight:600;color:var(--muted);}
.leg-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}
.hbar-list{display:flex;flex-direction:column;gap:.5rem;}
.hbar-row{display:flex;align-items:center;gap:.5rem;}
.hbar-label{font-size:.72rem;color:var(--muted);width:72px;text-align:right;flex-shrink:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.hbar-track{flex:1;height:10px;background:var(--bg);border-radius:99px;overflow:hidden;}
.hbar-fill{height:100%;border-radius:99px;transition:width .7s ease;}
.hbar-val{font-size:.7rem;font-weight:700;color:var(--muted);width:58px;flex-shrink:0;text-align:right;}
</style>
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
    <span class="hide-xs">ET</span>
  </a>
  <span class="nav-book-pill">📒 <?=htmlspecialchars($bookName)?></span>
  <div class="navbar-spacer"></div>
  <ul class="navbar-menu">
    <li><a href="dashboard.php<?=$qs?"?$qs":''?>" class="nav-btn">Dashboard</a></li>
    <li><a href="books.php" class="nav-btn">Books</a></li>
    <li><a href="logout.php" class="nav-btn nav-logout">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a></li>
  </ul>
  <button class="dark-toggle" id="darkToggle" title="Toggle dark mode">🌙</button>
</nav>

<div class="page-wrap">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.65rem;flex-wrap:wrap;" class="fade-up">
    <h1 style="font-family:var(--font-head);font-size:1.2rem;font-weight:700;">📊 Charts</h1>
    <div style="display:flex;gap:.4rem;">
      <a href="export.php?<?=$qs?>&type=csv" class="btn btn-outline btn-sm">⬇ CSV</a>
      <a href="export.php?<?=$qs?>&type=pdf" class="btn btn-outline btn-sm">⬇ PDF</a>
    </div>
  </div>

  <!-- Filter -->
  <div class="filter-bar fade-up-2">
    <div class="filter-top">
      <span class="filter-label-tag">📅 <?=htmlspecialchars($filter_label)?></span>
      <div class="filter-chips">
        <a href="?filter=fy"    class="filter-chip <?=$filter_mode==='fy'?'active':''?>">This FY</a>
        <a href="?filter=month" class="filter-chip <?=$filter_mode==='month'?'active':''?>">Month</a>
        <a href="?filter=all"   class="filter-chip <?=$filter_mode==='all'?'active':''?>">All</a>
        <span class="filter-chip <?=$filter_mode==='fysel'?'active':''?>" onclick="toggleExpand('fyRow')">Other FY ▾</span>
        <span class="filter-chip <?=$filter_mode==='custom'?'active':''?>" onclick="toggleExpand('customRow')">Custom ▾</span>
      </div>
    </div>
    <div class="filter-expand <?=$filter_mode==='fysel'?'show':''?>" id="fyRow" style="flex-direction:row;gap:.5rem;">
      <select id="fySelect" style="flex:1;padding:.38rem .65rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);background:var(--surface2);color:var(--ink);font-family:var(--font-body);font-size:.82rem;">
        <option value="">Select FY</option>
        <?php foreach($fy_options as $o): ?><option value="<?=htmlspecialchars($o['value'])?>" data-label="<?=htmlspecialchars($o['label'])?>"><?=htmlspecialchars($o['label'])?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-sm" onclick="applyFY()">Go</button>
    </div>
    <div class="filter-expand <?=$filter_mode==='custom'?'show':''?>" id="customRow">
      <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;width:100%;">
        <input type="hidden" name="filter" value="custom">
        <!-- ✅ FIX: use $date_from/$date_to so inputs are pre-filled correctly -->
        <input type="date" name="from" value="<?=htmlspecialchars($date_from)?>" max="<?=$today?>" style="font-family:var(--font-body);font-size:.82rem;padding:.38rem .65rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);background:var(--surface2);color:var(--ink);flex:1;min-width:130px;">
        <span style="color:var(--subtle);font-size:.8rem;">to</span>
        <input type="date" name="to" value="<?=htmlspecialchars($date_to)?>" max="<?=$today?>" style="font-family:var(--font-body);font-size:.82rem;padding:.38rem .65rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);background:var(--surface2);color:var(--ink);flex:1;min-width:130px;">
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
      </form>
    </div>
  </div>

  <?php if(empty($data_source)&&empty($monthly)): ?>
  <div class="empty-state card fade-up-3">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    <p>No data for <strong><?=htmlspecialchars($filter_label)?></strong>.<br>Add some transactions first.</p>
  </div>
  <?php else: ?>

  <div class="charts-grid fade-up-3">
    <?php if(!empty($data_source)): ?>
    <div class="chart-card">
      <h3>Expense by Category <span class="total-pill">₹<?=number_format($total_exp,0)?></span></h3>
      <canvas id="donutChart" style="max-height:240px;"></canvas>
      <div class="legend-grid" id="donutLeg"></div>
    </div>
    <?php endif; ?>
    <?php if(!empty($monthly)): ?>
    <div class="chart-card">
      <h3>Monthly Trend</h3>
      <canvas id="barChart" style="max-height:260px;"></canvas>
    </div>
    <?php endif; ?>
    <div class="chart-card">
      <h3>6-Month Trend <span class="total-pill" style="background:var(--brand-light);color:var(--brand);">Last 6 Months</span></h3>
      <canvas id="trendChart" style="max-height:240px;"></canvas>
    </div>
    <?php if(!empty($cat_breakdown)): ?>
    <div class="chart-card">
      <h3>Category Breakdown <span class="total-pill">&#8377;<?=number_format($totalCatExp,0)?></span></h3>
      <div class="hbar-list">
        <?php foreach($cat_breakdown as $i=>$row):
          $pct=$totalCatExp>0?round(($row['total']/$totalCatExp)*100):0;
          $col=$base_colors[$i%count($base_colors)];
        ?>
        <div class="hbar-row">
          <div class="hbar-label" title="<?=htmlspecialchars($row['category_name'])?>"><?=htmlspecialchars($row['category_name']?:'-')?></div>
          <div class="hbar-track"><div class="hbar-fill" style="width:<?=$pct?>%;background:<?=$col?>"></div></div>
          <div class="hbar-val">&#8377;<?=number_format($row['total'],0)?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<style>@media(max-width:400px){.hide-xs{display:none;}}</style>
<script>
const darkBtn=document.getElementById('darkToggle');
function applyTheme(t){document.documentElement.setAttribute('data-theme',t);darkBtn.textContent=t==='dark'?'☀️':'🌙';localStorage.setItem('et_theme',t);}
applyTheme(localStorage.getItem('et_theme')||'light');
darkBtn.addEventListener('click',()=>{const c=document.documentElement.getAttribute('data-theme');applyTheme(c==='dark'?'light':'dark');});
function toggleExpand(id){['fyRow','customRow'].forEach(r=>{if(r!==id)document.getElementById(r).classList.remove('show');});document.getElementById(id).classList.toggle('show');}
function applyFY(){const s=document.getElementById('fySelect');const v=s.value;const l=s.options[s.selectedIndex]?.dataset?.label||'';if(!v)return;window.location.href='?filter=fysel&fyval='+encodeURIComponent(v)+'&fylabel='+encodeURIComponent(l);}

<?php if(!empty($data_source)): ?>
(function(){
  const labels=<?=json_encode(array_keys($data_source))?>;
  const data=<?=json_encode(array_values($data_source))?>;
  const colors=<?=json_encode($bg_cols)?>;
  const total=<?=$total_exp?>;
  new Chart(document.getElementById('donutChart'),{type:'doughnut',data:{labels,datasets:[{data,backgroundColor:colors,borderWidth:2,borderColor:'transparent',hoverOffset:6}]},options:{responsive:true,cutout:'60%',plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>{const p=((ctx.parsed/total)*100).toFixed(1);return ` ${ctx.label}: ₹${ctx.parsed.toLocaleString('en-IN')} (${p}%)`;}}}}}});
  const leg=document.getElementById('donutLeg');
  labels.forEach((l,i)=>{const p=((data[i]/total)*100).toFixed(1);leg.innerHTML+=`<div class="leg-item"><span class="leg-dot" style="background:${colors[i]}"></span>${l} <strong>${p}%</strong></div>`;});
})();
<?php endif; ?>

<?php if(!empty($monthly)): ?>
(function(){
  const labels=<?=json_encode($month_labels)?>;
  const inc=<?=json_encode($month_inc)?>;
  const exp=<?=json_encode($month_exp)?>;
  new Chart(document.getElementById('barChart'),{type:'bar',data:{labels,datasets:[{label:'Income',data:inc,backgroundColor:'rgba(22,163,74,.75)',borderRadius:5,borderSkipped:false},{label:'Expense',data:exp,backgroundColor:'rgba(220,38,38,.7)',borderRadius:5,borderSkipped:false}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{usePointStyle:true,padding:14,font:{size:11}}},tooltip:{callbacks:{label:ctx=>' ₹'+ctx.parsed.y.toLocaleString('en-IN')}}},scales:{y:{grid:{color:getComputedStyle(document.documentElement).getPropertyValue('--border')||'#e5e7eb'},ticks:{callback:v=>'₹'+v.toLocaleString('en-IN'),font:{size:10}}},x:{grid:{display:false},ticks:{font:{size:10}}}}}});
})();
<?php endif; ?>

(function(){
  const labels=<?=json_encode($trend_labels)?>;
  const inc=<?=json_encode($trend_inc)?>;
  const exp=<?=json_encode($trend_exp)?>;
  new Chart(document.getElementById('trendChart'),{type:'line',data:{labels,datasets:[{label:'Income',data:inc,borderColor:'#16a34a',backgroundColor:'rgba(22,163,74,.1)',tension:0.4,fill:true,pointRadius:4,pointBackgroundColor:'#16a34a'},{label:'Expense',data:exp,borderColor:'#dc2626',backgroundColor:'rgba(220,38,38,.08)',tension:0.4,fill:true,pointRadius:4,pointBackgroundColor:'#dc2626'}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{usePointStyle:true,padding:14,font:{size:11}}},tooltip:{callbacks:{label:ctx=>' ₹'+ctx.parsed.y.toLocaleString('en-IN')}}},scales:{y:{grid:{color:'#e5e7eb'},ticks:{callback:v=>'₹'+v.toLocaleString('en-IN'),font:{size:10}}},x:{grid:{display:false},ticks:{font:{size:10}}}}}});
})();
</script>
</body>
</html>