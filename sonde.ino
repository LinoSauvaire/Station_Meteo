// Weather Station
// Librairies necessaires
#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>

// WiFi Connection
const char* ssid = "iPhone";
const char* password = "05062001";

// API Configuration
const char* api_url = "http://172.20.10.5/weather_api/api.php";

// Pin Configuration
#define BME_SDA 18
#define BME_SCL 19

// Structure data
struct WeatherData {
    float temperature_celsius;
    float pressure_hpa;
    float humidity_percent;
};

// Object du circuit
Adafruit_BME280 bme;

// Interval entre les mesures/envois
const unsigned long SEND_INTERVAL = 10000;  // 10 seconds
unsigned long lastSendTime = 0;

// Setup WiFi avec timeout
void setup_wifi() {
    delay(10);
    Serial.println("\nConnecting to WiFi...");
    Serial.printf("SSID: %s\n", ssid);

    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, password);

    unsigned long startAttemptTime = millis();

    while (WiFi.status() != WL_CONNECTED && millis() - startAttemptTime < 10000) {
        delay(500);
        Serial.print(".");
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\nWiFi connected successfully!");
        Serial.print("IP address: ");
        Serial.println(WiFi.localIP());
    } else {
        Serial.println("\nWiFi connection FAILED");
    }
}

// foncion pour lire les valeurs du capteur bme
WeatherData readSensorData() {
    WeatherData data;
    data.temperature_celsius = bme.readTemperature();
    data.pressure_hpa = bme.readPressure() / 100.0F;
    data.humidity_percent = bme.readHumidity();
    return data;
}

//Checkup des valeurs mesurées
bool isValidData(const WeatherData& data) {
    return !isnan(data.temperature_celsius) && 
           !isnan(data.pressure_hpa) && 
           !isnan(data.humidity_percent) &&
           data.temperature_celsius > -40.0 && data.temperature_celsius < 85.0 &&
           data.pressure_hpa > 300.0 && data.pressure_hpa < 1100.0 &&
           data.humidity_percent >= 0.0 && data.humidity_percent <= 100.0;
}

//Affichage des valeurs mesurées
void printMeasurements(const WeatherData& data) {
    Serial.println("\nCurrent Measurements:");
    Serial.printf("Temperature: %.1f °C\n", data.temperature_celsius);
    Serial.printf("Pressure: %.1f hPa\n", data.pressure_hpa);
    Serial.printf("Humidity: %.1f %%\n", data.humidity_percent);
}

//Envoi des données à l'API
bool sendDataToAPI(const WeatherData& data) {
    if(WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi not connected");
        return false;
    }

    HTTPClient http;
    http.begin(api_url);
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<200> doc;
    doc["temperature"] = data.temperature_celsius;
    doc["pressure"] = data.pressure_hpa;
    doc["humidity"] = data.humidity_percent;

    String jsonString;
    serializeJson(doc, jsonString);

    Serial.print("Sending data to API: ");
    Serial.println(jsonString);

    int httpResponseCode = http.POST(jsonString);

    if(httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("HTTP Response code: " + String(httpResponseCode));
        Serial.println("Response: " + response);
        http.end();
        return true;
    } else {
        Serial.print("Error on sending POST: ");
        Serial.println(httpResponseCode);
        http.end();
        return false;
    }
}

//Initialisation ESP32
void setup() {
    Serial.begin(115200);
    delay(2000);
    Serial.println("\nWeather Station Starting...");

    // Initialize I2C
    Wire.begin();
    Serial.println("I2C initialized");
    
    // Initialize BME280
    if (!bme.begin(0x76)) {
        Serial.println("Could not find BME280 sensor!");
        Serial.println("Check your I2C address (default: 0x76)");
        Serial.println("Check your wiring (SDA: 18, SCL: 19)");
        while (1) delay(10);
    }
    Serial.println("BME280 sensor initialized successfully");

    // Configuration BME280
    bme.setSampling(Adafruit_BME280::MODE_NORMAL,
                    Adafruit_BME280::SAMPLING_X2,
                    Adafruit_BME280::SAMPLING_X16,
                    Adafruit_BME280::SAMPLING_X1,
                    Adafruit_BME280::FILTER_X16,
                    Adafruit_BME280::STANDBY_MS_500);

    // Setup WiFi
    setup_wifi();
}

//Boucle principale
void loop() {
    // Check WiFi connexion
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi connection lost. Reconnecting...");
        setup_wifi();
    }

    // Lire et envoyer les données
    if (millis() - lastSendTime > SEND_INTERVAL) {
        WeatherData data = readSensorData();
        
        if (isValidData(data)) {
            printMeasurements(data);
            if (sendDataToAPI(data)) {
                lastSendTime = millis();
            }
        } else {
            Serial.println("Invalid sensor readings detected!");
        }
    }
}
