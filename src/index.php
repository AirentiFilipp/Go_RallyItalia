<?php
// ── Rally Italia — index.php ─────────────────────────────
// Shell HTML principale. Tutti i dati arrivano via API JS.
session_start();
?><!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rally Italia — Classifiche Live</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏎️</text></svg>">
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Loading screen -->
<div id="loading">
  <div class="l-logo"><span>Rally</span> Italia</div>
  <div class="l-sub">🏁 Classifiche Live</div>
  <div class="l-spinner"><span class="spinner"></span></div>
</div>

<!-- Toast -->
<div id="toast"></div>

<!-- ══ ADMIN LOGIN MODAL ══════════════════════════════════ -->
<div class="overlay" id="adminModal">
  <div class="modal">
    <h3>🔐 Accesso Admin</h3>
    <div class="form-row">
      <label>Password</label>
      <input type="password" id="adminPwd" placeholder="Inserisci password…">
    </div>
    <p class="form-note" style="margin-bottom:0">Password demo: <strong>admin123</strong></p>
    <div class="modal-foot">
      <button class="btn btn-outline" onclick="closeModal('adminModal')">Annulla</button>
      <button class="btn btn-primary" onclick="doAdminLogin()">Accedi</button>
    </div>
  </div>
</div>

<!-- ══ DELETE CONFIRM MODAL ══════════════════════════════ -->
<div class="overlay" id="deleteModal">
  <div class="modal">
    <h3>⚠️ Conferma Eliminazione</h3>
    <p style="color:var(--muted);font-size:.9rem">Sei sicuro? L'operazione è irreversibile.</p>
    <div class="modal-foot">
      <button class="btn btn-outline" onclick="closeModal('deleteModal')">Annulla</button>
      <button class="btn btn-danger" onclick="doDelete()">Elimina</button>
    </div>
  </div>
</div>

<!-- ══ HEADER ═════════════════════════════════════════════ -->
<header class="header">
  <div class="logo">
    <span class="accent">Rally</span> Italia
    <em class="sub">🏁 Classifiche Live</em>
  </div>
  <nav class="nav">
    <button class="nav-btn active" id="navHome" onclick="showPage('home')">Calendario</button>
    <button class="nav-btn" id="navInsert" onclick="showPage('insert')">➕ Inserisci</button>
    <span class="admin-badge" id="adminToggle" onclick="toggleAdmin()">ADMIN OFF</span>
  </nav>
</header>

<!-- ══════════════════════════════════════════════════════
     PAGE: HOME — CALENDARIO
══════════════════════════════════════════════════════ -->
<div class="page active" id="page-home">

  <div class="hero">
    <h1>Campionato Italiano Rally 2025</h1>
    <p>Classifiche in tempo reale · Inserisci i tuoi tempi · Sfida gli altri piloti</p>
  </div>

  <div class="section">
    <div class="section-title">📅 Calendario Gare</div>

    <!-- Admin: aggiungi gara -->
    <div id="adminAddRace" style="display:none;margin-bottom:1.5rem">
      <div class="admin-bar">⚡ PANNELLO ADMIN — Gestione Gare</div>
      <button class="btn btn-outline btn-sm" onclick="toggleAddRaceForm()">➕ Nuova Gara</button>

      <div id="addRaceForm" style="display:none;margin-top:.8rem">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.2rem;max-width:640px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem">
            <div class="form-row">
              <label>Nome Gara *</label>
              <input type="text" id="nrName" placeholder="Es. Rally di Sanremo">
            </div>
            <div class="form-row">
              <label>Luogo *</label>
              <input type="text" id="nrLoc" placeholder="Es. Liguria">
            </div>
            <div class="form-row">
              <label>Data *</label>
              <input type="text" id="nrDate" placeholder="Es. 15-16 Apr">
            </div>
            <div class="form-row">
              <label>Fondo</label>
              <select id="nrSurface">
                <option>Asfalto</option><option>Terra</option>
                <option>Neve</option><option>Misto</option>
              </select>
            </div>
            <div class="form-row">
              <label>Stato</label>
              <select id="nrStatus">
                <option value="live">🟢 In Corso</option>
                <option value="upcoming" selected>🟡 Prossima</option>
                <option value="done">⚫ Conclusa</option>
              </select>
            </div>
            <div class="form-row" style="grid-column:1/-1">
              <label>Prove Speciali * (una per riga — Es: PS1: Langan (14.5 km))</label>
              <textarea id="nrPS" rows="4" placeholder="PS1: Langan (14.5 km)&#10;PS2: Cipressa (10.2 km)&#10;PS3: Monte Bignone (8.7 km)"></textarea>
            </div>
          </div>
          <div style="margin-top:.8rem;display:flex;gap:.5rem">
            <button class="btn btn-primary btn-sm" onclick="addRace()">💾 Salva Gara</button>
            <button class="btn btn-outline btn-sm" onclick="toggleAddRaceForm()">Annulla</button>
          </div>
        </div>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Data</th>
            <th>Gara</th>
            <th>Luogo</th>
            <th>Fondo</th>
            <th>Stato</th>
            <th>Azioni</th>
          </tr>
        </thead>
        <tbody id="calendarBody">
          <tr><td colspan="6" style="text-align:center;padding:2rem">
            <span class="spinner"></span>
          </td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     PAGE: RACE — CLASSIFICA
══════════════════════════════════════════════════════ -->
<div class="page" id="page-race">

  <div class="race-header">
    <div>
      <div style="font-family:var(--font-d);font-size:.73rem;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);margin-bottom:.3rem">
        <span style="cursor:pointer;color:var(--red)" onclick="showPage('home')">← Calendario</span>
      </div>
      <h2 id="raceTitle">—</h2>
      <div class="race-meta" id="raceMeta"></div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="showPage('insert', currentRace?.id)">➕ Inserisci Tempo</button>
  </div>

  <div class="ps-bar">
    <label>PS:</label>
    <select id="psSelect" style="max-width:280px"></select>
    <div style="flex:1"></div>
    <label>Categoria:</label>
    <div class="cat-tabs" id="catTabs"></div>
  </div>

  <div class="section" style="padding-top:1rem">

    <!-- Pending (admin) -->
    <div id="pendingSection" style="display:none;margin-bottom:1.5rem">
      <div class="admin-bar">⏳ Tempi in attesa di approvazione</div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>Pilota</th><th>Vettura</th><th>Cat.</th><th>Tempo</th><th>Azioni</th>
          </tr></thead>
          <tbody id="pendingBody"></tbody>
        </table>
      </div>
    </div>

    <div class="section-title" id="leaderboardTitle">🏆 Classifica — Rally2</div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Pilota / Utente</th>
            <th>Vettura</th>
            <th>Tempo</th>
            <th>Distacco</th>
            <th id="adminActionsHeader" style="display:none">Admin</th>
          </tr>
        </thead>
        <tbody id="leaderboardBody">
          <tr><td colspan="6" style="text-align:center;padding:2rem">
            <span class="spinner"></span>
          </td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     PAGE: INSERT — MODULO INSERIMENTO
══════════════════════════════════════════════════════ -->
<div class="page" id="page-insert">
  <div class="section">
    <div class="form-card">
      <div class="form-title">⏱️ Inserisci un <span class="accent">Tempo</span></div>

      <div class="form-row">
        <label>Gara</label>
        <select id="insRace" onchange="updateInsertStages()"></select>
      </div>
      <div class="form-row">
        <label>Prova Speciale</label>
        <select id="insStage"></select>
      </div>
      <div class="form-row">
        <label>Nome Pilota / Utente</label>
        <input type="text" id="insDriver" placeholder="Es. @Marco_99">
      </div>
      <div class="form-row">
        <label>Vettura</label>
        <input type="text" id="insCar" placeholder="Es. Skoda Fabia RS Rally2">
      </div>
      <div class="form-row">
        <label>Categoria</label>
        <select id="insCat">
          <option>WRC</option>
          <option selected>Rally2</option>
          <option>Rally3</option>
          <option>Rally4</option>
          <option>N5</option>
          <option>Historic</option>
        </select>
      </div>
      <div class="form-row">
        <label>Condizioni Meteo / Fondo</label>
        <select id="insWeather">
          <option>☀️ Soleggiato / Asciutto</option>
          <option>🌥️ Nuvoloso</option>
          <option>🌧️ Pioggia / Bagnato</option>
          <option>❄️ Neve / Ghiaccio</option>
          <option>🌫️ Nebbia</option>
        </select>
      </div>
      <div class="form-row">
        <label>Tempo Effettuato</label>
        <div class="time-row">
          <div class="t-unit">
            <label style="font-size:.68rem">MIN</label>
            <input type="number" id="insMin" min="0" max="99" value="10">
          </div>
          <div class="t-sep">:</div>
          <div class="t-unit">
            <label style="font-size:.68rem">SEC</label>
            <input type="number" id="insSec" min="0" max="59" value="45">
          </div>
          <div class="t-sep">.</div>
          <div class="t-unit">
            <label style="font-size:.68rem">DECIMI</label>
            <input type="number" id="insDec" min="0" max="9" value="2">
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn btn-primary" id="insertBtn" onclick="insertTime()">💾 Salva Tempo</button>
        <button class="btn btn-outline" onclick="resetInsertForm()">Reset</button>
      </div>
      <p class="form-note" id="insertNote">I tempi vengono approvati dall'admin prima di apparire in classifica.</p>
    </div>
  </div>
</div>

<script>
// Esponi currentRace al template inline onclick
function getCurrent() { return typeof currentRace !== 'undefined' ? currentRace : null; }
</script>
<script src="script.js"></script>
</body>
</html>
