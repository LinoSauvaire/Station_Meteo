-- Drop & create database station_meteo
DROP DATABASE IF EXISTS station_meteo;
CREATE DATABASE IF NOT EXISTS station_meteo
    CHARACTER SET = 'utf8mb4'
    COLLATE = 'utf8mb4_unicode_ci';

USE station_meteo;

-- Create table for sensor
CREATE TABLE IF NOT EXISTS sondes (
    id_sonde INT NOT NULL AUTO_INCREMENT,
    nom_sonde VARCHAR(50) NOT NULL,
    location VARCHAR(100),
    date_installation DATE,
    PRIMARY KEY (id_sonde)
) ENGINE=InnoDB;

-- Create table for weather readings
CREATE TABLE IF NOT EXISTS readings (
    id_mesure BIGINT AUTO_INCREMENT,
    id_sonde INT NOT NULL,
    reading_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    temperature DECIMAL(5,2) NOT NULL,  -- Range: -999.99 to 999.99°C
    humidity DECIMAL(4,1) NOT NULL,     -- Range: 0.0 to 100.0%
    pression DECIMAL(6,1) NOT NULL,     -- Range: 0.0 to 9999.9 hPa
    PRIMARY KEY (id_mesure),
    INDEX idx_reading_time (reading_time),
    INDEX idx_sonde (id_sonde),
    CONSTRAINT fk_readings_sonde
        FOREIGN KEY (id_sonde)
        REFERENCES sondes(id_sonde)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Create view for latest readings
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
WHERE r.reading_time = (
    SELECT MAX(reading_time)
    FROM readings
    WHERE id_sonde = r.id_sonde
);

-- Insert our sensor
INSERT INTO sondes (nom_sonde, location, date_installation) VALUES
('sonde1', 'CESI Aix', CURRENT_DATE);

-- Pas sure de cette partie pour récolter les donnees de l'esp32
-- Test for saving values in MariaDB
import paho.mqtt.client as mqtt
import mysql.connector
from mysql.connector import Error
import json
import traceback
import sys

# Add detailed error logging
def log_error():
    with open('/home/pi/mqtt-logger/error.log', 'w') as f:
        traceback.print_exc(file=f)
        print("Detailed error logged to error.log")

#Create user to check the files
DROP USER IF EXIST 'pi'@'localhost';
CREATE USER IF NOT EXISTS 'pi'@'localhost' IDENTIFIED BY 'meteo';
GRANT ALL PRIVILEGES ON station_meteo.* TO 'pi'@'localhost';
FLUSH PRIVILEGES;

# MariaDB Connection Configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'your_username',  # Replace with your actual MariaDB username
    'password': 'your_password',  # Replace with your actual MariaDB password
    'database': 'station_meteo'
}

# MQTT Configuration
MQTT_BROKER = '172.20.10.5'  # Verify this IP is correct
MQTT_PORT = 1883
MQTT_TOPIC = 'sonde1'

# Sonde ID (pour la sonde )
SONDE_ID = 1

def on_connect(client, userdata, flags, rc):
    print(f"Connected with result code {rc}")
    client.subscribe(MQTT_TOPIC)

def on_message(client, userdata, msg):
    try:
        # Parse JSON message
        payload = json.loads(msg.payload.decode())
        
        # Extract data
        temperature = payload.get('temperature')
        humidity = payload.get('humidity')
        pressure = payload.get('pressure')
        
        # Validate data
        if all(v is not None for v in [temperature, humidity, pressure]):
            # Connect to database
            connection = mysql.connector.connect(**DB_CONFIG)
            cursor = connection.cursor()
            
            # Insert reading
            insert_query = """
            INSERT INTO readings 
            (id_sonde, temperature, humidity, pression) 
            VALUES (%s, %s, %s, %s)
            """
            values = (SONDE_ID, temperature, humidity, pressure)
            
            try:
                cursor.execute(insert_query, values)
                connection.commit()
                print(f"Inserted: Temp {temperature}°C, Humidity {humidity}%, Pressure {pressure}hPa")
            except Error as e:
                log_error()
                print(f"Error inserting data: {e}")
            finally:
                cursor.close()
                connection.close()
        else:
            print("Incomplete data received")
    
    except Exception as e:
        log_error()
        print(f"Unexpected error: {e}")

# Setup MQTT Client
client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

try:
    # Connect to MQTT Broker
    client.connect(MQTT_BROKER, MQTT_PORT, 60)

    # Run the loop
    print("Starting MQTT to MariaDB data logger...")
    client.loop_forever()
except Exception as e:
    log_error()
    print(f"Fatal error: {e}")
    sys.exit(1)
