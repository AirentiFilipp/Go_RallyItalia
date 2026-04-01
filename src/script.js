cat > src/script.js << 'ENDOFFILE'
/* Rally Italia — Frontend JS */

const API = '/api';

let isAdmin      = false;
let currentRace  = null;
let currentStage = null;
let currentCat   = 'Rally2';
let deleteTarget = null;
let allRaces     = [];

document.addEventListener('DOMContentLoaded', async () => {
  await checkAdminStatus();
  await loadCalendar();
  hideLoading();
});

async function api(method, path, body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin' };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(API + path, opts);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
  return data;
}

function hideLoading() {
  const el = document.getElementById('loading');
  if (!el) return;
  el.style.opacity = '0';
  setTimeout(() => el.remove(), 400);
}

function showPage(page, data = null) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');
  if (page === 'home') {
    document.getElementById('navHome').classList.add('active');
    loadCalendar();
  } else if (page === 'race') {
    loadRacePage(data);
  } else if (page === 'insert') {
    document.getElementById('navInsert').classList.add('active');
    prepareInsertForm(data);
  }
}

async function checkAdminStatus() {
  try {
    const r = await api('GET', '/auth/status');
    isAdmin = r.is_admin;
    updateAdminBadge();
  } catch { }
}

function toggleAdmin() {
  if (isAdmin) { logoutAdmin(); }
  else {
    document.getElementById('adminModal').classList.add('open');
    setTimeout(() => document.getElementById('adminPwd').focus(), 80);
  }
}

async function doAdminLogin() {
  const pwd = document.getElementById('adminPwd').value;
  try {
    await api('POST', '/auth/login', { password: pwd });
    isAdmin = true;
    closeModal('adminModal');
    document.getElementById('adminPwd').value = '';
    updateAdminBadge();
    toast('⚡ Modalità Admin attivata!');
    refreshCurrentPage();
  } catch (e) {
    toast(e.message, true);
    document.getElementById('adminPwd').value = '';
  }
}

async function logoutAdmin() {
  await api('POST', '/auth/logout');
  isAdmin = false;
  updateAdminBadge();
  toast('Admin disattivato.');
  refreshCurrentPage();
}

function updateAdminBadge() {
  const badge = document.getElementById('adminToggle');
  badge.textContent = isAdmin ? 'ADMIN ON' : 'ADMIN OFF';
  badge.classList.toggle('on', isAdmin);
}

function refreshCurrentPage() {
  const active = document.querySelector('.page.active')?.id;
  if (active === 'page-home') loadCalendar();
  else if (active === 'page-race' && currentRace) loadRacePage(currentRace.id, false);
}

async function loadCalendar() {
  const tbody = document.getElementById('calendarBody');
  tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem"><span class="spinner"></span></td></tr>';
  try {
    allRaces = await api('GET', '/races');
    renderCalendar();
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty"><div class="emo">⚠️</div><p>${e.message}</p></div></td></tr>`;
  }
}

function renderCalendar() {
  const tbody = document.getElementById('calendarBody');
  const adminAddRace = document.getElementById('adminAddRace');
  if (adminAddRace) adminAddRace.style.display = isAdmin ? 'block' : 'none';
  if (!allRaces.length) {
    tbody.innerHTML = '<tr><td colspan="6"><div class="empty"><div class="emo">🏁</div><p>Nessuna gara nel calendario.</p></div></td></tr>';
    return;
  }
  const SURFACE = { Asfalto:'🛣️', Terra:'🟫', Neve:'❄️', Misto:'🔀' };
  tbody.innerHTML = allRaces.map(r => {
    const badgeHtml = r.status === 'live'
      ? '<span class="badge badge-live">In Corso</span>'
      : r.status === 'upcoming'
      ? '<span class="badge badge-upcoming">Prossima</span>'
      : '<span class="badge badge-done">Conclusa</span>';
    let actions = `<button class="btn btn-outline btn-sm" onclick="showPage('race',${r.id})">Classifica</button>`;
    if (r.status === 'live') {
      actions = `<button class="btn btn-primary btn-sm" onclick="showPage('race',${r.id})">🏆 Classifica</button>
                 <button class="btn btn-outline btn-sm" onclick="showPage('insert',${r.id})">➕ Inserisci</button>`;
    }
    const adminDel = isAdmin
      ? `<button class="btn btn-danger btn-sm" onclick="confirmDeleteRace(${r.id},'${esc(r.name)}')">🗑️</button>`
      : '';
    return `<tr>
      <td style="font-family:var(--font-d);font-weight:600;color:var(--muted)">${esc(r.date_label)}</td>
      <td><strong style="font-family:var(--font-d);font-size:1.05rem">${esc(r.name)}</strong></td>
      <td style="color:var(--muted);font-size:.88rem">📍 ${esc(r.location)}</td>
      <td style="font-size:.85rem;color:var(--muted)">${SURFACE[r.surface]||''} ${r.surface}</td>
      <td>${badgeHtml}</td>
      <td><div class="actions">${actions}${adminDel}</div></td>
    </tr>`;
  }).join('');
}

async function loadRacePage(raceId, resetCat = true) {
  if (resetCat) currentCat = 'Rally2';
  try {
    currentRace = await api('GET', `/races/${raceId}`);
  } catch (e) { toast(e.message, true); return; }
  document.getElementById('raceTitle').textContent = currentRace.name;
  const SURFACE = { Asfalto:'🛣️', Terra:'🟫', Neve:'❄️', Misto:'🔀' };
  document.getElementById('raceMeta').innerHTML = `
    <span class="meta-pill">${SURFACE[currentRace.surface]||'🛣️'} ${currentRace.surface}</span>
    <span class="meta-pill">📍 ${esc(currentRace.location)}</span>
    <span class="meta-pill">📅 ${esc(currentRace.date_label)}</span>`;
  const psSelect = document.getElementById('psSelect');
  psSelect.innerHTML = currentRace.stages.map(s =>
    `<option value="${s.id}">${esc(s.name)}${s.km ? ' ('+s.km+' km)' : ''}</option>`
  ).join('');
  if (currentRace.stages.length) currentStage = currentRace.stages[0].id;
  psSelect.onchange = () => { currentStage = parseInt(psSelect.value); loadLeaderboard(); };
  await loadLeaderboard();
}

async function loadLeaderboard() {
  if (!currentRace || !currentStage) return;
  const tbody = document.getElementById('leaderboardBody');
  tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem"><span class="spinner"></span></td></tr>';
  try {
    const times = await api('GET', `/races/${currentRace.id}/times?stage_id=${currentStage}&category=${encodeURIComponent(currentCat)}`);
    renderLeaderboard(times);
    const allTimes = await api('GET', `/races/${currentRace.id}/times?stage_id=${currentStage}`);
    buildCatTabs(allTimes);
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty"><div class="emo">⚠️</div><p>${e.message}</p></div></td></tr>`;
  }
}

function renderLeaderboard(times) {
  document.getElementById('leaderboardTitle').textContent = `🏆 Classifica — ${currentCat}`;
  document.getElementById('adminActionsHeader').style.display = isAdmin ? '' : 'none';
  const approved = times.filter(t => t.approved == 1);
  const pending  = times.filter(t => t.approved == 0);
  const pendSec  = document.getElementById('pendingSection');
  if (isAdmin && pending.length) {
    pendSec.style.display = 'block';
    document.getElementById('pendingBody').innerHTML = pending.map(t => `
      <tr class="pending-row">
        <td><span class="driver-name">${esc(t.driver)}</span> <span class="badge badge-pending">Pending</span></td>
        <td style="color:var(--muted);font-size:.85rem">${esc(t.car)}</td>
        <td><span class="badge badge-cat">${t.category}</span></td>
        <td><span class="time-val">${fmtTime(t)}</span></td>
        <td><div class="actions">
          <button class="btn btn-gold btn-sm" onclick="approveTime(${t.id})">✓ Approva</button>
          <button class="btn btn-danger btn-sm" onclick="confirmDeleteTime(${t.id})">✗ Rifiuta</button>
        </div></td>
      </tr>`).join('');
  } else { pendSec.style.display = 'none'; }
  const tbody = document.getElementById('leaderboardBody');
  if (!approved.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty"><div class="emo">⏱️</div><p>Nessun tempo approvato per questa categoria.<br>Sii il primo!</p></div></td></tr>`;
    return;
  }
  approved.sort((a,b) => toTenths(a) - toTenths(b));
  const best = toTenths(approved[0]);
  tbody.innerHTML = approved.map((t, i) => {
    const pos = i + 1;
    const posClass = pos <= 3 ? `pos-${pos}` : '';
    const medal = pos === 1 ? '🥇' : pos === 2 ? '🥈' : pos === 3 ? '🥉' : pos;
    const gap = pos === 1
      ? '<span class="gap-leader">— Leader</span>'
      : `<span class="gap-val">+${((toTenths(t) - best) / 10).toFixed(1)}s</span>`;
    const adminCol = isAdmin
      ? `<td><button class="btn btn-danger btn-sm" onclick="confirmDeleteTime(${t.id})">🗑️</button></td>`
      : '';
    return `<tr>
      <td><span class="rank-pos ${posClass}">${medal}</span></td>
      <td><div class="driver-name">${esc(t.driver)}</div><div class="driver-info">${esc(t.weather)}</div></td>
      <td style="font-size:.83rem;color:var(--muted)">${esc(t.car)}</td>
      <td><span class="time-val${pos===1?' best':''}">${fmtTime(t)}</span></td>
      <td>${gap}</td>
      ${adminCol}
    </tr>`;
  }).join('');
}

function buildCatTabs(allTimes) {
  const cats = [...new Set(allTimes.map(t => t.category))];
  const order = ['WRC','Rally2','Rally3','Rally4','N5','Historic'];
  const sorted = order.filter(c => cats.includes(c));
  if (!sorted.includes('Rally2')) sorted.unshift('Rally2');
  document.getElementById('catTabs').innerHTML = sorted.map(c =>
    `<button class="cat-tab${c === currentCat ? ' active' : ''}" onclick="setCat('${c}')">${c}</button>`
  ).join('');
}

function setCat(cat) {
  currentCat = cat;
  document.querySelectorAll('.cat-tab').forEach(t => t.classList.toggle('active', t.textContent === cat));
  loadLeaderboard();
}

async function approveTime(id) {
  try {
    await api('PUT', `/times/${id}/approve`);
    toast('✅ Tempo approvato!');
    loadLeaderboard();
  } catch(e) { toast(e.message, true); }
}

async function prepareInsertForm(raceId = null) {
  if (!allRaces.length) allRaces = await api('GET', '/races');
  if (!allRaces.length) return;
  const raceSelect = document.getElementById('insRace');
  raceSelect.innerHTML = allRaces.map(r => `<option value="${r.id}">${esc(r.name)}</option>`).join('');
  if (raceId) raceSelect.value = raceId;
  else if (currentRace) raceSelect.value = currentRace.id;
  if (raceSelect.value) await updateInsertStages();
  updateInsertNote();
}

async function updateInsertStages() {
  const raceId = document.getElementById('insRace').value;
  if (!raceId) return;
  try {
    const stages = await api('GET', `/races/${raceId}/stages`);
    document.getElementById('insStage').innerHTML = stages.map(s =>
      `<option value="${s.id}">${esc(s.name)}${s.km ? ' ('+s.km+' km)':''}</option>`
    ).join('');
  } catch { document.getElementById('insStage').innerHTML = '<option>—</option>'; }
}

function updateInsertNote() {
  document.getElementById('insertNote').textContent = isAdmin
    ? '⚡ Admin: i tuoi tempi vengono approvati automaticamente.'
    : 'I tempi vengono approvati dall\'admin prima di comparire in classifica.';
}

async function insertTime() {
  const raceId  = parseInt(document.getElementById('insRace').value);
  const stageId = parseInt(document.getElementById('insStage').value);
  const driver  = document.getElementById('insDriver').value.trim();
  const car     = document.getElementById('insCar').value.trim();
  const cat     = document.getElementById('insCat').value;
  const weather = document.getElementById('insWeather').value;
  const min     = parseInt(document.getElementById('insMin').value) || 0;
  const sec     = parseInt(document.getElementById('insSec').value) || 0;
  const dec     = parseInt(document.getElementById('insDec').value) || 0;
  if (!driver) { toast('⚠️ Inserisci il nome del pilota.', true); return; }
  if (!car)    { toast('⚠️ Inserisci la vettura.', true); return; }
  if (min === 0 && sec === 0) { toast('⚠️ Tempo non valido.', true); return; }
  const btn = document.getElementById('insertBtn');
  btn.disabled = true; btn.textContent = 'Salvataggio…';
  try {
    const result = await api('POST', '/times', {
      race_id: raceId, stage_id: stageId, driver, car,
      category: cat, weather, time_min: min, time_sec: sec, time_dec: dec
    });
    toast(result.message || '✅ Tempo salvato!');
    resetInsertForm();
    if (currentRace?.id === raceId) setTimeout(() => showPage('race', raceId), 900);
  } catch(e) {
    toast(e.message, true);
  } finally {
    btn.disabled = false; btn.textContent = '💾 Salva Tempo';
  }
}

function resetInsertForm() {
  ['insDriver','insCar'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('insMin').value = 10;
  document.getElementById('insSec').value = 45;
  document.getElementById('insDec').value = 2;
}

function toggleAddRaceForm() {
  const f = document.getElementById('addRaceForm');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

async function addRace() {
  const name    = document.getElementById('nrName').value.trim();
  const loc     = document.getElementById('nrLoc').value.trim();
  const date    = document.getElementById('nrDate').value.trim();
  const surface = document.getElementById('nrSurface').value;
  const status  = document.getElementById('nrStatus').value;
  const psRaw   = document.getElementById('nrPS').value.trim();
  if (!name || !loc || !date) { toast('⚠️ Compila i campi obbligatori.', true); return; }
  const stages = psRaw.split('\n').map(line => {
    const m = line.trim().match(/^(.+?)\(?([\d.]+)\s*km\)?/i);
    return m ? { name: m[1].trim().replace(/:$/, ''), km: parseFloat(m[2]) } : { name: line.trim(), km: null };
  }).filter(s => s.name);
  if (!stages.length) { toast('⚠️ Aggiungi almeno una PS.', true); return; }
  try {
    await api('POST', '/races', { name, location: loc, date_label: date, surface, status, stages });
    toast('✅ Gara aggiunta!');
    ['nrName','nrLoc','nrDate','nrPS'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('addRaceForm').style.display = 'none';
    await loadCalendar();
  } catch(e) { toast(e.message, true); }
}

function confirmDeleteTime(id) {
  deleteTarget = { type: 'time', id };
  document.getElementById('deleteModal').classList.add('open');
}
function confirmDeleteRace(id, name) {
  deleteTarget = { type: 'race', id, name };
  document.getElementById('deleteModal').classList.add('open');
}
async function doDelete() {
  if (!deleteTarget) return;
  try {
    if (deleteTarget.type === 'time') {
      await api('DELETE', `/times/${deleteTarget.id}`);
      toast('🗑️ Tempo eliminato.');
      loadLeaderboard();
    } else {
      await api('DELETE', `/races/${deleteTarget.id}`);
      toast('🗑️ Gara eliminata.');
      loadCalendar();
    }
  } catch(e) { toast(e.message, true); }
  closeModal('deleteModal');
  deleteTarget = null;
}

function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

document.addEventListener('click', e => {
  if (e.target.classList.contains('overlay')) e.target.classList.remove('open');
});
document.addEventListener('keydown', e => {
  if (e.key === 'Enter' && document.getElementById('adminModal').classList.contains('open')) doAdminLogin();
});

function toTenths(t) {
  return parseInt(t.time_min) * 600 + parseInt(t.time_sec) * 10 + parseInt(t.time_dec);
}
function fmtTime(t) {
  return `${parseInt(t.time_min)}:${String(parseInt(t.time_sec)).padStart(2,'0')}.${parseInt(t.time_dec)}`;
}
function esc(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

let toastTimer;
function toast(msg, err = false) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.classList.toggle('err', err);
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 3200);
}
ENDOFFILE