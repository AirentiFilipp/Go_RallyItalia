-- ── Rally Italia — Schema DB ────────────────────────────
-- Eseguito automaticamente da Docker al primo avvio

SET NAMES utf8mb4;
SET time_zone = '+01:00';

-- Tabella Gare
CREATE TABLE IF NOT EXISTS races (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    location    VARCHAR(80)  NOT NULL,
    date_label  VARCHAR(40)  NOT NULL,
    surface     ENUM('Asfalto','Terra','Neve','Misto') DEFAULT 'Asfalto',
    status      ENUM('live','upcoming','done') DEFAULT 'upcoming',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella Prove Speciali
CREATE TABLE IF NOT EXISTS special_stages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    race_id     INT UNSIGNED NOT NULL,
    name        VARCHAR(120) NOT NULL,
    km          DECIMAL(5,2),
    sort_order  TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (race_id) REFERENCES races(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella Tempi
CREATE TABLE IF NOT EXISTS times (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    race_id     INT UNSIGNED NOT NULL,
    stage_id    INT UNSIGNED NOT NULL,
    driver      VARCHAR(80)  NOT NULL,
    car         VARCHAR(100) NOT NULL,
    category    ENUM('WRC','Rally2','Rally3','Rally4','N5','Historic') DEFAULT 'Rally2',
    time_min    TINYINT UNSIGNED NOT NULL,
    time_sec    TINYINT UNSIGNED NOT NULL,
    time_dec    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    weather     VARCHAR(50)  DEFAULT 'Asciutto',
    approved    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (race_id)  REFERENCES races(id) ON DELETE CASCADE,
    FOREIGN KEY (stage_id) REFERENCES special_stages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indici per performance
CREATE INDEX idx_times_race_stage_cat ON times(race_id, stage_id, category, approved);
CREATE INDEX idx_stages_race          ON special_stages(race_id);
