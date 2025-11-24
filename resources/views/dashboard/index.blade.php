<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard - สรุปข้อมูลครัวเรือน</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- เพิ่ม plugin datalabels (เฉพาะเพื่อแสดงตัวเลขบนแท่ง areaChart) -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <style>
      .chart-card { height: 420px; }
      .chart-canvas { width:100%; height:100%; }

      /* บังคับความสูงเฉพาะของแคนวาสบนกราฟ "จำนวนครัวเรือน (areaChart)" ให้คงที่
         เพื่อไม่ให้ขยายเมื่อ filter โดยไม่แตะกราฟอื่น ๆ */
      #areaChart { width:100% !important; height:260px !important; display:block; }
    </style>
</head>
<body class="p-3">
<div class="container-fluid">
    <h3>Dashboard สรุปข้อมูลครัวเรือน</h3>

    <div class="row mb-3">
        <div class="col-md-4">
            <label>เลือกระดับพื้นที่</label>
            <select id="areaLevel" class="form-select">
                <option value="province">จังหวัด</option>
                <option value="district" selected>อำเภอ</option>
                <option value="subdistrict">ตำบล</option>
                <option value="village">หมู่บ้าน</option>
            </select>
        </div>
        <div class="col-md-8 d-flex align-items-end justify-content-end">
            <div id="statusSummary" class="text-end">
                <div>ทั้งหมด: <span id="totalCount">0</span></div>
                <div>ผ่าน: <span id="passedCount">0</span></div>
                <div>ไม่ผ่าน: <span id="failedCount">0</span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Area chart (ผ่าน / ไม่ผ่าน) -->
        <div class="col-md-12 mb-4">
            <div class="card p-2">
                <canvas id="areaChart" height="140"></canvas>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card chart-card p-2">
                <canvas id="genderChart" class="chart-canvas"></canvas>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card chart-card p-2">
                <canvas id="ageChart" class="chart-canvas"></canvas>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card p-2">
                <canvas id="financeChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
/*
  เพิ่มการเปลี่ยนแปลงเฉพาะที่ขอ:
  - ไม่เปลี่ยนขนาดของกราฟอื่น ๆ
  - ล็อกขนาดของ areaChart โดย CSS (#areaChart height)
  - ทำให้ areaChart (จำนวนครัวเรือน: ผ่าน/ไม่ผ่าน) เป็นสองแท่งแยกกัน (grouped)
    และแสดงตัวเลขบนแท่งทันที (ใช้ chartjs-plugin-datalabels)
*/

// helper for fetch
async function fetchJson(url){ const r = await fetch(url); if(!r.ok) throw new Error('Fetch error '+r.status); return r.json(); }
function debounce(fn, wait){ let t; return function(...a){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,a), wait); }; }

// ให้ plugin datalabels ถูกลงทะเบียน (ปลอดภัย)
try { if (typeof ChartDataLabels !== 'undefined') Chart.register(ChartDataLabels); } catch(e){ console.warn('datalabels register failed', e); }

// เก็บ instance ของ charts ที่เราต้องการจัดการ
let areaChartInstance = null;
let genderChartInstance = null;
let ageChartInstance = null;
let financeChartInstance = null;

// สร้าง function สำหรับ area chart ที่เป็น grouped bars + datalabels
function buildAreaGroupedBar(ctxOrEl, labels, passedData, failedData, title){
    return new Chart(ctxOrEl, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'ผ่าน', data: passedData, backgroundColor: '#1cc88a' },
                { label: 'ไม่ผ่าน', data: failedData, backgroundColor: '#e74a3b' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // ให้ขนาดมาจาก CSS ของ #areaChart (ที่ล็อกไว้)
            plugins: {
                title: { display: true, text: title },
                legend: { position: 'top' },
                datalabels: {
                    color: '#fff',
                    anchor: 'center',
                    align: 'center',
                    formatter: v => (v === null ? '' : v),
                    font: { weight: '700', size: 12 }
                }
            },
            scales: {
                x: { stacked: false, ticks: { maxRotation:45, autoSkip: true } },
                y: { beginAtZero: true }
            }
        },
        plugins: (typeof ChartDataLabels !== 'undefined') ? [ChartDataLabels] : []
    });
}

// ฟังก์ชันโหลดข้อมูลและวาดกราฟจำนวนครัวเรือน (แทนที่ฟังก์ชันเดิม loadArea)
async function loadArea(level){
    try {
        const data = await fetchJson('/api/dashboard/area?level=' + encodeURIComponent(level));
        console.log('DEBUG area passfail raw:', data);

        if(!Array.isArray(data) || data.length === 0){
            if(areaChartInstance){ areaChartInstance.destroy(); areaChartInstance = null; }
            return;
        }

        // แสดง top N เพื่อให้ไม่ล้นหน้าจอ (ปรับ TOP ตามต้องการ)
        const TOP = 12;
        const top = data.slice(0, TOP);
        const labels = top.map(r => r.area);
        const passed = top.map(r => r.passed);
        const failed = top.map(r => r.failed);

        // ทำลาย chart เก่า (ถ้ามี) แล้วสร้างใหม่
        if(areaChartInstance) { try{ areaChartInstance.destroy(); } catch(e){} areaChartInstance = null; }
        const canvas = document.getElementById('areaChart');
        areaChartInstance = buildAreaGroupedBar(canvas, labels, passed, failed, 'จำนวนครัวเรือน - ' + level);
    } catch(err){
        console.error('loadArea error', err);
    }
}

// ฟังก์ชันอื่น ๆ ไม่เปลี่ยนแปลง — คัดลอกจากโค้ดคุณเดิม (เรียก API เดิม)
async function loadStatus(){
    try {
        const s = await fetchJson('/api/dashboard/status');
        document.getElementById('totalCount').innerText = s.total;
        document.getElementById('passedCount').innerText = s.passed;
        document.getElementById('failedCount').innerText = s.failed;
    } catch(e){ console.error('status load error', e); }
}

function buildPie(ctx, labels, data, title){
    return new Chart(ctx, {
        type: 'pie',
        data: { labels, datasets: [{ data, backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b'] }]},
        options: { responsive:true, plugins:{ title:{ display:true, text:title } }, maintainAspectRatio:false }
    });
}

function buildFinanceChart(ctx, labels, incomes, expenses, debts, povertyLines, title){
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { type: 'line', label: 'เส้นความยากจน (Poverty line)', data: povertyLines, borderColor:'#5555ff', borderWidth:2, fill:false, tension:0.1, yAxisID:'y' },
                { type: 'bar', label: 'Avg Income', data: incomes, backgroundColor: '#1cc88a', yAxisID:'y' },
                { type: 'bar', label: 'Avg Expense', data: expenses, backgroundColor: '#e74a3b', yAxisID:'y' },
                { type: 'bar', label: 'Avg Debt', data: debts, backgroundColor: '#f6c23e', yAxisID:'y' },
            ]
        },
        options: {
            responsive:true,
            plugins:{ title:{ display:true, text:title } },
            scales: {
                y: { beginAtZero:true }
            },
            maintainAspectRatio:false
        }
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    // initial loads
    await loadStatus();

    // draw other charts same as before (minimal changes)
    // gender
    try {
        const genderData = await fetchJson('/api/dashboard/gender');
        const gLabels = genderData.map(r => r.gender);
        const gValues = genderData.map(r => r.total);
        const genCtx = document.getElementById('genderChart').getContext('2d');
        if(genderChartInstance) { try{ genderChartInstance.destroy(); }catch(e){} }
        genderChartInstance = buildPie(genCtx, gLabels, gValues, 'จำนวนตามเพศ');
    } catch(e){ console.error('gender load error', e); }

    // age
    try {
        const ageData = await fetchJson('/api/dashboard/age');
        const aLabels = ageData.map(r => r.range);
        const aValues = ageData.map(r => r.total);
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        if(ageChartInstance){ try{ ageChartInstance.destroy(); }catch(e){} }
        ageChartInstance = buildPie(ageCtx, aLabels, aValues, 'จำนวนตามช่วงอายุ');
    } catch(e){ console.error('age load error', e); }

    // finance
    try {
        const fin = await fetchJson('/api/dashboard/finances');
        const labels = fin.map(r => r.province || 'ไม่ระบุ');
        const incomes = fin.map(r => r.avg_income || 0);
        const expenses = fin.map(r => r.avg_expense || 0);
        const debts = fin.map(r => r.avg_debt || 0);
        const poverty = fin.map(r => r.poverty_line === null ? 0 : r.poverty_line);
        const finCtx = document.getElementById('financeChart').getContext('2d');
        if(financeChartInstance){ try{ financeChartInstance.destroy(); }catch(e){} }
        financeChartInstance = buildFinanceChart(finCtx, labels, incomes, expenses, debts, poverty, 'รายได้/รายจ่าย/หนี้เฉลี่ยตามจังหวัด (เทียบเส้นความยากจน)');
    } catch(e){ console.error('finance load error', e); }

    // area (ผ่าน/ไม่ผ่าน) initial
    const initialLevel = document.getElementById('areaLevel').value || 'district';
    await loadArea(initialLevel);

    // change level event: เมื่อ filter เปลี่ยน ให้โหลด area ใหม่ โดยขนาด canvas จะไม่ขยายเพราะ CSS ล็อกไว้
    document.getElementById('areaLevel').addEventListener('change', async (e) => {
        const lvl = e.target.value;
        await loadArea(lvl);
    });
});
</script>
</body>
</html>