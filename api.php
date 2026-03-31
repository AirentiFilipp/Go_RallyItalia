<?php
// ── Rally Italia — API REST ──────────────────────────────
// Tutte le chiamate passano per questo file via .htaccess

session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Routing ──────────────────────────────────────────────
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path   = preg_replace('#^/api#', '', $path);   // rimuove prefisso /api
$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    // ── AUTH ─────────────────────────────────────────────
    if ($path === '/auth/login' && $method === 'POST') {
        $pwd = $body['password'] ?? '';
        if ($pwd === ADMIN_PASSWORD) {
            $_SESSION['is_admin'] = true;
            jsonResponse(['ok' => true, 'message' => 'Login effettuato.']);
        }
        jsonResponse(['error' => 'Password errata.'], 401);
    }

    if ($path === '/auth/logout' && $method === 'POST') {
        session_destroy();
        jsonResponse(['ok' => true]);
    }

    if ($path === '/auth/status' && $method === 'GET') {
        jsonResponse(['is_admin' => isAdmin()]);
    }

    // ── RACES ────────────────────────────────────────────
    if ($path === '/races' && $method === 'GET') {
        $rows = getDB()->query("
            SELECT r.*, COUNT(s.id) AS stage_count
            FROM races r
            LEFT JOIN special_stages s ON s.race_id = r.id
            GROUP BY r.id
            ORDER BY r.id
        ")->fetchAll();
        jsonResponse($rows);
    }

    if ($path === '/races' && $method === 'POST') {
        requireAdmin();
        $name    = trim($body['name'] ?? '');
        $loc     = trim($body['location'] ?? '');
        $date    = trim($body['date_label'] ?? '');
        $surface = $body['surface'] ?? 'Asfalto';
        $status  = $body['status'] ?? 'upcoming';
        $stages  = $body['stages'] ?? [];  // array di {name, km}

        if (!$name || !$loc || !$date) jsonResponse(['error' => 'Campi obbligatori mancanti.'], 422);

        $db = getDB();
        $db->beginTransaction();

        $stmt = $db->prepare("INSERT INTO races (name, location, date_label, surface, status) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $loc, $date, $surface, $status]);
        $raceId = (int)$db->lastInsertId();

        $stStmt = $db->prepare("INSERT INTO special_stages (race_id, name, km, sort_order) VALUES (?,?,?,?)");
        foreach ($stages as $i => $s) {
            $stStmt->execute([$raceId, $s['name'], $s['km'] ?? null, $i + 1]);
        }

        $db->commit();
        jsonResponse(['ok' => true, 'id' => $raceId], 201);
    }

    // GET /races/{id}
    if (preg_match('#^/races/(\d+)$#', $path, $m) && $method === 'GET') {
        $raceId = (int)$m[1];
        $race = getDB()->prepare("SELECT * FROM races WHERE id = ?");
        $race->execute([$raceId]);
        $row = $race->fetch();
        if (!$row) jsonResponse(['error' => 'Gara non trovata.'], 404);

        $stages = getDB()->prepare("SELECT * FROM special_stages WHERE race_id = ? ORDER BY sort_order");
        $stages->execute([$raceId]);
        $row['stages'] = $stages->fetchAll();
        jsonResponse($row);
    }

    // PUT /races/{id}  (modifica stato, nome, ecc.)
    if (preg_match('#^/races/(\d+)$#', $path, $m) && $method === 'PUT') {
        requireAdmin();
        $raceId = (int)$m[1];
        $fields = [];
        $params = [];
        foreach (['name','location','date_label','surface','status'] as $f) {
            if (isset($body[$f])) { $fields[] = "$f = ?"; $params[] = $body[$f]; }
        }
        if (!$fields) jsonResponse(['error' => 'Nessun campo da aggiornare.'], 422);
        $params[] = $raceId;
        getDB()->prepare("UPDATE races SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
        jsonResponse(['ok' => true]);
    }

    // DELETE /races/{id}
    if (preg_match('#^/races/(\d+)$#', $path, $m) && $method === 'DELETE') {
        requireAdmin();
        getDB()->prepare("DELETE FROM races WHERE id = ?")->execute([(int)$m[1]]);
        jsonResponse(['ok' => true]);
    }

    // ── STAGES ───────────────────────────────────────────
    // GET /races/{id}/stages
    if (preg_match('#^/races/(\d+)/stages$#', $path, $m) && $method === 'GET') {
        $stmt = getDB()->prepare("SELECT * FROM special_stages WHERE race_id = ? ORDER BY sort_order");
        $stmt->execute([(int)$m[1]]);
        jsonResponse($stmt->fetchAll());
    }

    // ── TIMES ────────────────────────────────────────────
    // GET /races/{id}/times?stage_id=X&category=Rally2
    if (preg_match('#^/races/(\d+)/times$#', $path, $m) && $method === 'GET') {
        $raceId   = (int)$m[1];
        $stageId  = isset($_GET['stage_id'])  ? (int)$_GET['stage_id']      : null;
        $category = isset($_GET['category'])  ? trim($_GET['category'])      : null;
        $showAll  = isAdmin();  // admin vede anche i pending

        $where  = ['t.race_id = ?'];
        $params = [$raceId];

        if ($stageId)  { $where[] = 't.stage_id = ?';  $params[] = $stageId; }
        if ($category) { $where[] = 't.category = ?';  $params[] = $category; }
        if (!$showAll) { $where[] = 't.approved = 1'; }

        $sql = "
            SELECT t.*, s.name AS stage_name, s.km AS stage_km
            FROM times t
            JOIN special_stages s ON s.id = t.stage_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY (t.time_min * 600 + t.time_sec * 10 + t.time_dec) ASC
        ";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        jsonResponse($stmt->fetchAll());
    }

    // POST /times  — inserimento nuovo tempo
    if ($path === '/times' && $method === 'POST') {
        $raceId   = (int)($body['race_id']   ?? 0);
        $stageId  = (int)($body['stage_id']  ?? 0);
        $driver   = trim($body['driver']     ?? '');
        $car      = trim($body['car']        ?? '');
        $category = $body['category']        ?? 'Rally2';
        $min      = (int)($body['time_min']  ?? 0);
        $sec      = (int)($body['time_sec']  ?? 0);
        $dec      = (int)($body['time_dec']  ?? 0);
        $weather  = trim($body['weather']    ?? 'Soleggiato');

        if (!$raceId || !$stageId || !$driver || !$car) {
            jsonResponse(['error' => 'Dati mancanti.'], 422);
        }
        if ($min === 0 && $sec === 0) {
            jsonResponse(['error' => 'Tempo non valido.'], 422);
        }

        $approved = isAdmin() ? 1 : 0;

        $stmt = getDB()->prepare("
            INSERT INTO times (race_id, stage_id, driver, car, category, time_min, time_sec, time_dec, weather, approved)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$raceId, $stageId, $driver, $car, $category, $min, $sec, $dec, $weather, $approved]);
        $newId = (int)getDB()->lastInsertId();

        jsonResponse([
            'ok'       => true,
            'id'       => $newId,
            'approved' => (bool)$approved,
            'message'  => $approved ? 'Tempo salvato e approvato.' : 'Tempo salvato. In attesa di approvazione.'
        ], 201);
    }

    // PUT /times/{id}/approve
    if (preg_match('#^/times/(\d+)/approve$#', $path, $m) && $method === 'PUT') {
        requireAdmin();
        getDB()->prepare("UPDATE times SET approved = 1 WHERE id = ?")->execute([(int)$m[1]]);
        jsonResponse(['ok' => true]);
    }

    // DELETE /times/{id}
    if (preg_match('#^/times/(\d+)$#', $path, $m) && $method === 'DELETE') {
        requireAdmin();
        getDB()->prepare("DELETE FROM times WHERE id = ?")->execute([(int)$m[1]]);
        jsonResponse(['ok' => true]);
    }

    // ── 404 ──────────────────────────────────────────────
    jsonResponse(['error' => "Endpoint non trovato: $method $path"], 404);

} catch (PDOException $e) {
    jsonResponse(['error' => 'Errore database: ' . $e->getMessage()], 500);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Errore server: ' . $e->getMessage()], 500);
}
