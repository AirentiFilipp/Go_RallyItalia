-- ── Rally Italia — Dati di esempio ─────────────────────

-- Gare
INSERT INTO races (name, location, date_label, surface, status) VALUES
('Rally di Sanremo',      'Liguria',   '15-16 Apr', 'Asfalto', 'live'),
('Rally Costa Smeralda',  'Sardegna',  '02-04 Mag', 'Terra',   'upcoming'),
('Rally del Salento',     'Puglia',    '20-21 Mag', 'Asfalto', 'upcoming');

-- Prove Speciali — Sanremo (race_id=1)
INSERT INTO special_stages (race_id, name, km, sort_order) VALUES
(1, 'PS1: Langan',        14.50, 1),
(1, 'PS2: Cipressa',      10.20, 2),
(1, 'PS3: Monte Bignone',  8.70, 3),
(1, 'PS4: Langan BIS',    14.50, 4);

-- Prove Speciali — Costa Smeralda (race_id=2)
INSERT INTO special_stages (race_id, name, km, sort_order) VALUES
(2, 'PS1: Tempio',   12.30, 1),
(2, 'PS2: Arzachena', 9.80, 2);

-- Prove Speciali — Salento (race_id=3)
INSERT INTO special_stages (race_id, name, km, sort_order) VALUES
(3, 'PS1: Lecce',   11.00, 1),
(3, 'PS2: Otranto',  7.50, 2);

-- Tempi di esempio — Sanremo PS1 (stage_id=1), Rally2
INSERT INTO times (race_id, stage_id, driver, car, category, time_min, time_sec, time_dec, weather, approved) VALUES
(1, 1, '@Marco_99',        'Skoda Fabia RS Rally2',     'Rally2', 10, 45, 2, 'Soleggiato', 1),
(1, 1, '@TuoNome (Admin)', 'Toyota GR Yaris Rally2',    'Rally2', 10, 48, 5, 'Soleggiato', 1),
(1, 1, '@RallyFanITA',     'Citroën C3 Rally2',         'Rally2', 10, 51, 0, 'Soleggiato', 1),
(1, 1, '@Luca_Drift',      'Hyundai i20 N Rally2',      'Rally2', 11,  2, 1, 'Soleggiato', 1),
(1, 1, '@Speedy_V',        'Ford Fiesta Rally4',        'Rally4', 11, 55, 8, 'Soleggiato', 1),
-- Tempo in attesa
(1, 1, '@NewDriver',       'Renault Clio Rally3',       'Rally3', 11, 10, 3, 'Soleggiato', 0);

-- Tempi PS2
INSERT INTO times (race_id, stage_id, driver, car, category, time_min, time_sec, time_dec, weather, approved) VALUES
(1, 2, '@Marco_99',    'Skoda Fabia RS Rally2',  'Rally2',  8, 12, 3, 'Soleggiato', 1),
(1, 2, '@RallyFanITA', 'Citroën C3 Rally2',      'Rally2',  8, 18, 0, 'Soleggiato', 1);
