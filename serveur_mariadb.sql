--BDD MariaDB
-- Création database weather_station
DROP DATABASE IF EXISTS weather_station;
CREATE DATABASE IF NOT EXISTS weather_station;
    CHARACTER SET = 'utf8mb4'
    COLLATE = 'utf8mb4_unicode_ci';

-- Creation de l'utilisateur pour recueillir les données via l'API
CREATE USER IF NOT EXISTS 'sonde1'@'localhost' IDENTIFIED BY 'meteo';
GRANT ALL PRIVILEGES ON weather_station.* TO 'sonde1'@'localhost';
FLUSH PRIVILEGES;
EXIT;

-- Creation table pour les sondes
CREATE TABLE IF NOT EXISTS sondes (
    id_sonde INT NOT NULL AUTO_INCREMENT,
    nom_sonde VARCHAR(50) NOT NULL,
    location VARCHAR(100),
    date_installation DATE,
    PRIMARY KEY (id_sonde)
) ENGINE=InnoDB;

-- Creation table pour les données des sondes
CREATE TABLE IF NOT EXISTS readings (
    id_mesure BIGINT AUTO_INCREMENT,
    id_sonde INT NOT NULL,
    reading_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    temperature DECIMAL(5,2) NOT NULL, --pourrait etre mis en (4,2) car max -99,99 ou 99,99 suffit pour les températures
    humidity DECIMAL(4,1) NOT NULL,
    pression DECIMAL(6,1) NOT NULL,
    PRIMARY KEY (id_mesure),
    INDEX idx_reading_time (reading_time),
    INDEX idx_sonde (id_sonde),
    CONSTRAINT fk_readings_sonde
        FOREIGN KEY (id_sonde)
        REFERENCES sondes(id_sonde)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Vue pour les 10 dernières données de la bdd
CREATE OR REPLACE VIEW latest_readings AS
SELECT 
    r.id_sonde,
    s.nom_sonde,
    r.reading_time,
    r.temperature,
    r.humidity,
    r.pression
FROM readings r
JOIN sondes s ON r.id_sonde = s.id_sonde
WHERE r.id_mesure IN (
    SELECT id_mesure
    FROM (
        SELECT id_mesure
        FROM readings
        ORDER BY reading_time DESC
        LIMIT 10
    ) AS sub
)
ORDER BY r.reading_time DESC;

-- Ajout de notre sonde
INSERT INTO sondes (nom_sonde, location, date_installation) VALUES
('sonde1', 'CESI Aix', CURRENT_DATE);
