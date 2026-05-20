<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MailBlast 郵件群發系統</title>
<style>
/* ── Reset & Base ─────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --bg:       #e1eeff;
  --surface:  #dce7fd;
  --surface2: #d4eafe;
  --border:   #2e3250;
  --accent:   #086776;
  --accent2:  #0a7c8e;
  --green:    #115c91;
  --red:      #ef4444;
  --yellow:   #f59e0b;
  --text:     #086776;
  --muted:    #086776;
  --radius:   12px;
}
body {
  font-family: 'Segoe UI', system-ui, sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
}

/* ── Layout ───────────────────────────────────────── */
.app { display: flex; height: 100vh; overflow: hidden; }
.sidebar {
  width: 220px; min-width: 220px;
  background: var(--surface);
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
  padding: 24px 0;
}
.sidebar-logo {
  padding: 0 20px 24px;
  font-size: 1.2rem; font-weight: 700;
  color: var(--accent);
  display: flex; align-items: center; gap: 10px;
}
.sidebar-logo svg { flex-shrink: 0; }
.nav-item {
  display: flex; align-items: center; gap: 12px;
  padding: 11px 20px; cursor: pointer;
  color: var(--muted); font-size: 0.9rem;
  border-left: 3px solid transparent;
  transition: all .15s;
  user-select: none;
}
.nav-item:hover { color: var(--text); background: var(--surface2); }
.nav-item.active {
  color: var(--accent); background: var(--surface2);
  border-left-color: var(--accent);
}
.main { flex: 1; overflow-y: auto; }
.page { display: none; padding: 32px; max-width: 960px; }
.page.active { display: block; }

/* ── Cards ────────────────────────────────────────── */
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 24px;
  margin-bottom: 20px;
}
.card-title {
  font-size: 1rem; font-weight: 600;
  margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}
.card-title .badge {
  background: var(--accent); color: #fff;
  font-size: .7rem; padding: 2px 8px;
  border-radius: 99px; font-weight: 500;
}

/* ── Form Controls ────────────────────────────────── */
.form-row { display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap; }
input[type=text], input[type=email], input[type=number],
select, textarea {
  background: var(--surface2);
  border: 1px solid var(--border);
  color: var(--text);
  border-radius: 8px;
  padding: 10px 14px;
  font-size: .9rem;
  outline: none;
  transition: border-color .15s;
  font-family: inherit;
}
input[type=text]:focus, input[type=email]:focus,
input[type=number]:focus, select:focus, textarea:focus {
  border-color: var(--accent);
}
input[type=text], input[type=email] { flex: 1; min-width: 180px; }
input[type=number] { width: 90px; }
select { min-width: 130px; }
textarea { width: 100%; resize: vertical; min-height: 100px; line-height: 1.6; }

/* ── Buttons ──────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 18px; border-radius: 8px; border: none;
  font-size: .875rem; font-weight: 600; cursor: pointer;
  transition: all .15s; white-space: nowrap;
}
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: #3b5bdb; }
.btn-success { background: var(--green); color: #fff; }
.btn-success:hover { background: #16a34a; }
.btn-danger  { background: var(--red); color: #fff; }
.btn-danger:hover  { background: #dc2626; }
.btn-ghost {
  background: transparent; color: var(--muted);
  border: 1px solid var(--border);
}
.btn-ghost:hover { color: var(--text); border-color: var(--muted); }
.btn:disabled { opacity: .45; cursor: not-allowed; }

/* ── Table ────────────────────────────────────────── */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: .875rem; }
th {
  text-align: left; padding: 10px 14px;
  color: var(--muted); font-weight: 600;
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
td { padding: 10px 14px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--surface2); }

/* ── Status Badges ────────────────────────────────── */
.badge-ok  { color: var(--green); }
.badge-err { color: var(--red); }
.chip {
  display: inline-block; padding: 2px 10px;
  border-radius: 99px; font-size: .75rem; font-weight: 600;
}
.chip-green { background: #14532d33; color: var(--green); }
.chip-red   { background: #7f1d1d33; color: var(--red); }

/* ── Progress ─────────────────────────────────────── */
.progress-wrap { margin: 16px 0; }
.progress-bar-bg {
  background: var(--surface2); border-radius: 99px; height: 12px;
  overflow: hidden; position: relative;
}
.progress-bar-fill {
  height: 100%; border-radius: 99px;
  background: linear-gradient(90deg, var(--accent), var(--accent2));
  transition: width .4s ease;
  width: 0%;
}
.progress-label {
  display: flex; justify-content: space-between;
  font-size: .8rem; color: var(--muted); margin-top: 6px;
}
.progress-pct { font-weight: 700; color: var(--accent); font-size: 1.1rem; }

/* ── Send Log Feed ────────────────────────────────── */
#send-feed {
  max-height: 300px; overflow-y: auto;
  background: var(--surface2); border-radius: 8px;
  padding: 12px; font-size: .82rem;
  line-height: 1.8; font-family: monospace;
}
#send-feed .row-ok  { color: var(--green); }
#send-feed .row-err { color: var(--red); }
#send-feed .row-info { color: var(--yellow); }

/* ── Stats ────────────────────────────────────────── */
.stats-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
.stat-box {
  flex: 1; min-width: 130px;
  background: var(--surface2); border-radius: var(--radius);
  padding: 18px 20px; text-align: center;
}
.stat-num { font-size: 2rem; font-weight: 800; }
.stat-lbl { color: var(--muted); font-size: .8rem; margin-top: 4px; }

/* ── Compose Area ─────────────────────────────────── */
.compose-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 700px) { .compose-grid { grid-template-columns: 1fr; } }
.compose-label { font-size: .8rem; color: var(--muted); margin-bottom: 6px; }
.compose-full { grid-column: 1/-1; }
.option-row { display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 16px; }
.option-group { display: flex; flex-direction: column; gap: 6px; }
.option-group label { font-size: .8rem; color: var(--muted); }

/* ── Toast ────────────────────────────────────────── */
#toast {
  position: fixed; bottom: 28px; right: 28px;
  background: var(--surface2); border: 1px solid var(--border);
  border-radius: 10px; padding: 14px 20px;
  font-size: .875rem; color: var(--text);
  transform: translateY(80px); opacity: 0;
  transition: all .25s; z-index: 999;
  max-width: 340px;
}
#toast.show { transform: translateY(0); opacity: 1; }
#toast.ok  { border-color: var(--green); color: var(--green); }
#toast.err { border-color: var(--red);   color: var(--red); }

/* ── Page Header ──────────────────────────────────── */
.page-header { margin-bottom: 28px; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; }
.page-header p  { color: var(--muted); font-size: .9rem; margin-top: 4px; }

/* ── Rich Text Toolbar ────────────────────────────── */
.toolbar {
  display: flex; gap: 4px; flex-wrap: wrap;
  padding: 8px; background: var(--surface2);
  border: 1px solid var(--border);
  border-bottom: none; border-radius: 8px 8px 0 0;
}
.toolbar button {
  background: none; border: none; color: var(--muted);
  cursor: pointer; padding: 5px 9px; border-radius: 5px;
  font-size: .85rem; transition: all .1s;
}
.toolbar button:hover { background: var(--border); color: var(--text); }
#editor {
  min-height: 180px; background: var(--surface2);
  border: 1px solid var(--border); border-radius: 0 0 8px 8px;
  padding: 14px; outline: none; color: var(--text);
  font-size: .9rem; line-height: 1.7;
}
</style>
</head>
<body>
<div class="app">

<!-- ── Sidebar ─────────────────────────────────────── -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
      <polyline points="22,6 12,13 2,6"/>
    </svg>
    MailBlast
  </div>
  <div class="nav-item active" onclick="showPage('compose')">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
    </svg>
    撰寫 / 寄送
  </div>
  <div class="nav-item" onclick="showPage('recipients')">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
      <circle cx="9" cy="7" r="4"/>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    </svg>
    收件人管理
  </div>
  <div class="nav-item" onclick="showPage('logs')">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <polyline points="14 2 14 8 20 8"/>
      <line x1="16" y1="13" x2="8" y2="13"/>
      <line x1="16" y1="17" x2="8" y2="17"/>
      <polyline points="10 9 9 9 8 9"/>
    </svg>
    寄送記錄
  </div>
</aside>

<!-- ── Main ────────────────────────────────────────── -->
<main class="main">

  <!-- 撰寫 / 寄送 -->
  <div id="page-compose" class="page active">
    <div class="page-header">
      <h1>✉️ 撰寫郵件</h1>
      <p>設定郵件內容與寄送方式，然後啟動群發</p>
    </div>

    <!-- 郵件內容 -->
    <div class="card">
      <div class="card-title">📝 郵件內容</div>
      <div class="compose-label">主旨</div>
      <input type="text" id="subject" placeholder="請輸入郵件主旨" style="width:100%;margin-bottom:14px;">

      <div class="compose-label">內容（支援 HTML 格式）</div>
      <div class="toolbar">
        <button onclick="fmt('bold')" title="粗體"><b>B</b></button>
        <button onclick="fmt('italic')" title="斜體"><i>I</i></button>
        <button onclick="fmt('underline')" title="底線"><u>U</u></button>
        <button onclick="fmt('strikeThrough')" title="刪除線"><s>S</s></button>
        <button onclick="fmt('insertUnorderedList')" title="項目清單">☰</button>
        <button onclick="fmt('insertOrderedList')" title="編號清單">1.</button>
        <button onclick="insertLink()" title="插入連結">🔗</button>
        <button onclick="fmt('removeFormat')" title="清除格式">✕</button>
      </div>
      <div id="editor" contenteditable="true" spellcheck="false">請在此輸入郵件內容...</div>
    </div>

    <!-- 寄送設定 -->
    <div class="card">
      <div class="card-title">⚙️ 寄送設定</div>
      <div class="option-row">
        <div class="option-group">
          <label>寄送模式</label>
          <select id="send-mode" onchange="toggleRandCount()">
            <option value="all">📨 全部寄送</option>
            <option value="random">🎲 隨機寄送</option>
          </select>
        </div>
        <div class="option-group" id="rand-count-wrap" style="display:none">
          <label>隨機筆數</label>
          <input type="number" id="rand-count" value="5" min="1" max="9999">
        </div>
        <div class="option-group">
          <label>最小間隔（秒）</label>
          <input type="number" id="min-delay" value="2" min="0" max="600">
        </div>
        <div class="option-group">
          <label>最大間隔（秒）</label>
          <input type="number" id="max-delay" value="5" min="0" max="600">
        </div>
      </div>
      <button class="btn btn-success" id="btn-send" onclick="startSend()">
        🚀 開始寄送
      </button>
    </div>

    <!-- 進度 -->
    <div class="card" id="progress-card" style="display:none">
      <div class="card-title">
        📊 寄送進度
        <span class="progress-pct" id="pct-label">0%</span>
      </div>
      <div class="progress-wrap">
        <div class="progress-bar-bg">
          <div class="progress-bar-fill" id="prog-bar"></div>
        </div>
        <div class="progress-label">
          <span id="prog-detail">準備中...</span>
          <span id="prog-count"></span>
        </div>
      </div>
      <div id="send-feed"></div>
    </div>
  </div>

  <!-- 收件人管理 -->
  <div id="page-recipients" class="page">
    <div class="page-header">
      <h1>👥 收件人管理</h1>
      <p>新增、匯入、管理所有收件人 Email</p>
    </div>

    <!-- 新增單筆 -->
    <div class="card">
      <div class="card-title">➕ 新增 Email</div>
      <div class="form-row">
        <input type="email" id="new-email" placeholder="email@example.com">
        <input type="text" id="new-name" placeholder="名稱（選填）" style="max-width:200px;">
        <button class="btn btn-primary" onclick="addEmail()">新增</button>
      </div>
    </div>

    <!-- 批次匯入 -->
    <div class="card">
      <div class="card-title">📋 批次匯入</div>
      <div class="compose-label">每行一個 Email（或用逗號、分號分隔）</div>
      <textarea id="bulk-emails" placeholder="user1@example.com&#10;user2@example.com&#10;user3@example.com"></textarea>
      <br><button class="btn btn-primary" style="margin-top:10px" onclick="bulkImport()">匯入</button>
    </div>

    <!-- 清單 -->
    <div class="card">
      <div class="card-title">
        📋 收件人清單
        <span class="badge" id="recipient-count">0</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No.</th>
              <th>Email</th>
              <th>名稱</th>
              <th>狀態</th>
              <th>加入時間</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody id="recipient-table"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- 寄送記錄 -->
  <div id="page-logs" class="page">
    <div class="page-header">
      <h1>📋 寄送記錄</h1>
      <p>最近 200 筆寄送紀錄</p>
    </div>
    <div class="stats-row">
      <div class="stat-box">
        <div class="stat-num" id="stat-total" style="color:var(--accent)">—</div>
        <div class="stat-lbl">總計</div>
      </div>
      <div class="stat-box">
        <div class="stat-num" id="stat-ok" style="color:var(--green)">—</div>
        <div class="stat-lbl">成功</div>
      </div>
      <div class="stat-box">
        <div class="stat-num" id="stat-fail" style="color:var(--red)">—</div>
        <div class="stat-lbl">失敗</div>
      </div>
    </div>
    <div class="card">
      <div class="card-title">
        🕒 寄送明細
        <button class="btn btn-ghost" onclick="loadLogs()" style="margin-left:auto;padding:6px 12px;font-size:.8rem">重新整理</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Email</th>
              <th>主旨</th>
              <th>狀態</th>
              <th>時間</th>
            </tr>
          </thead>
          <tbody id="log-table"></tbody>
        </table>
      </div>
    </div>
  </div>

</main>
</div>

<!-- Toast -->
<div id="toast"></div>

<script>
// ── Navigation ─────────────────────────────────────
function showPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  event.currentTarget.classList.add('active');
  if (name === 'recipients') loadRecipients();
  if (name === 'logs')       loadLogs();
}

// ── Toast ──────────────────────────────────────────
function toast(msg, type = 'ok') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'show ' + type;
  setTimeout(() => el.className = '', 3200);
}

// ── Recipients ─────────────────────────────────────
async function apiPost(action, fields = {}) {
  const fd = new FormData();
  fd.append('action', action);
  Object.entries(fields).forEach(([k, v]) => fd.append(k, v));

  const res = await fetch('api.php', { method: 'POST', body: fd });
  const text = await res.text();

  try {
    return JSON.parse(text);
  } catch (e) {
    console.error('api.php 回傳不是 JSON：', text);
    throw new Error('後端回傳格式錯誤，請查看 Console / Network 的 api.php Response');
  }
}

async function loadRecipients() {
  try {
    const data = await apiPost('get_recipients');
    if (!data.success) return toast(data.msg || '讀取收件人失敗', 'err');

    const tbody = document.getElementById('recipient-table');
    document.getElementById('recipient-count').textContent = data.total;
    tbody.innerHTML = '';
    data.data.forEach(r => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.no}</td>
        <td>${r.email}</td>
        <td>${r.name || '<span style="color:var(--muted)">—</span>'}</td>
        <td><span class="chip ${r.status==='active'?'chip-green':'chip-red'}">${r.status==='active'?'啟用':'停用'}</span></td>
        <td style="color:var(--muted);font-size:.8rem">${r.created_at}</td>
        <td><button class="btn btn-danger" style="padding:5px 10px;font-size:.78rem" onclick="deleteRecipient(${r.no})">刪除</button></td>
      `;
      tbody.appendChild(tr);
    });
  } catch (err) {
    console.error(err);
    toast(err.message || '讀取收件人失敗', 'err');
  }
}

async function addEmail() {
  const email = document.getElementById('new-email').value.trim();
  const name  = document.getElementById('new-name').value.trim();
  if (!email) return toast('請輸入 Email', 'err');

  try {
    const data = await apiPost('add_email', { email, name });
    toast(data.msg || '完成', data.success ? 'ok' : 'err');
    if (data.success) {
      document.getElementById('new-email').value = '';
      document.getElementById('new-name').value  = '';
      loadRecipients();
    }
  } catch (err) {
    console.error(err);
    toast(err.message || '新增失敗', 'err');
  }
}

async function bulkImport() {
  const emails = document.getElementById('bulk-emails').value;
  if (!emails.trim()) return toast('請輸入 Email 清單', 'err');

  try {
    const data = await apiPost('bulk_import', { emails });
    toast(data.msg || '完成', data.success ? 'ok' : 'err');
    if (data.success) {
      document.getElementById('bulk-emails').value = '';
      loadRecipients();
    }
  } catch (err) {
    console.error(err);
    toast(err.message || '匯入失敗', 'err');
  }
}

async function deleteRecipient(id) {
  if (!confirm('確定刪除？')) return;

  try {
    const data = await apiPost('delete_recipient', { id });
    if (!data.success) return toast(data.msg || '刪除失敗', 'err');
    loadRecipients();
  } catch (err) {
    console.error(err);
    toast(err.message || '刪除失敗', 'err');
  }
}

// ── Logs ───────────────────────────────────────────
async function loadLogs() {
  try {
    const data = await apiPost('get_logs');
    if (!data.success) return toast(data.msg || '讀取寄送記錄失敗', 'err');

    const tbody = document.getElementById('log-table');
    tbody.innerHTML = '';
    let ok = 0, fail = 0;
    data.data.forEach((r, i) => {
      if (r.status === 'success') ok++; else fail++;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="color:var(--muted)">${i+1}</td>
        <td>${r.email}</td>
        <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.subject}</td>
        <td><span class="chip ${r.status==='success'?'chip-green':'chip-red'}">${r.status==='success'?'✓ 成功':'✗ 失敗'}</span></td>
        <td style="color:var(--muted);font-size:.8rem">${r.sent_at}</td>
      `;
      tbody.appendChild(tr);
    });
    const total = data.data.length;
    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-ok').textContent    = ok;
    document.getElementById('stat-fail').textContent  = fail;
  } catch (err) {
    console.error(err);
    toast(err.message || '讀取寄送記錄失敗', 'err');
  }
}

// ── Rich Text ──────────────────────────────────────
function fmt(cmd, val) { document.execCommand(cmd, false, val || null); }
function insertLink() {
  const url = prompt('輸入連結網址：', 'https://');
  if (url) fmt('createLink', url);
}

// ── Send Mode Toggle ───────────────────────────────
function toggleRandCount() {
  const m = document.getElementById('send-mode').value;
  document.getElementById('rand-count-wrap').style.display = m === 'random' ? 'flex' : 'none';
}

// ── Start Send (SSE) ───────────────────────────────
let sending = false;
function startSend() {
  if (sending) return;
  const subject  = document.getElementById('subject').value.trim();
  const bodyHtml = document.getElementById('editor').innerHTML.trim();
  if (!subject)  return toast('請輸入主旨', 'err');
  if (!bodyHtml) return toast('請輸入郵件內容', 'err');

  const mode     = document.getElementById('send-mode').value;
  const count    = document.getElementById('rand-count').value;
  const minDelay = document.getElementById('min-delay').value;
  const maxDelay = document.getElementById('max-delay').value;

  // Show progress card
  document.getElementById('progress-card').style.display = 'block';
  document.getElementById('send-feed').innerHTML = '';
  setProgress(0, '準備中...', '');
  document.getElementById('btn-send').disabled = true;
  sending = true;

  const params = new URLSearchParams({
    mode, count,
    min_delay: minDelay,
    max_delay: maxDelay,
    subject: subject,
    body: bodyHtml,
  });

  const es = new EventSource('send.php?' + params.toString());

  es.addEventListener('start', e => {
    const d = JSON.parse(e.data);
    addFeedRow('info', `🚀 ${d.msg}`);
  });

  es.addEventListener('progress', e => {
    const d = JSON.parse(e.data);
    const pct = d.percent;
    setProgress(pct, `正在寄送第 ${d.current}/${d.total} 封 → ${d.email}`, `✓ ${d.success}  ✗ ${d.failed}`);
    if (d.status === 'success') {
      addFeedRow('ok', `✓ ${d.email}`);
    } else {
      addFeedRow('err', `✗ ${d.email}  [${d.error}]`);
    }
  });

  es.addEventListener('delay', e => {
    const d = JSON.parse(e.data);
    addFeedRow('info', `⏳ ${d.msg}`);
  });

  es.addEventListener('done', e => {
    const d = JSON.parse(e.data);
    setProgress(100, d.msg, `✓ ${d.success}  ✗ ${d.failed}`);
    addFeedRow('ok', `🏁 ${d.msg}`);
    es.close();
    sending = false;
    document.getElementById('btn-send').disabled = false;
    toast(d.msg, 'ok');
  });

  es.addEventListener('error', e => {
    try {
      const d = JSON.parse(e.data);
      toast(d.msg, 'err');
    } catch(_) {}
    es.close();
    sending = false;
    document.getElementById('btn-send').disabled = false;
  });

  es.onerror = () => {
    es.close();
    sending = false;
    document.getElementById('btn-send').disabled = false;
  };
}

function setProgress(pct, detail, count) {
  document.getElementById('prog-bar').style.width   = pct + '%';
  document.getElementById('pct-label').textContent  = pct + '%';
  document.getElementById('prog-detail').textContent = detail;
  document.getElementById('prog-count').textContent  = count;
}

function addFeedRow(cls, msg) {
  const feed = document.getElementById('send-feed');
  const div  = document.createElement('div');
  div.className = 'row-' + cls;
  div.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
  feed.appendChild(div);
  feed.scrollTop = feed.scrollHeight;
}
</script>
</body>
</html>
