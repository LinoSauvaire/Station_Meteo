//Weather Station
//Librairies needed
#include <Wire.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>

// WiFi Credentials
const char* ssid = "iPhone";
const char* password = "05062001";

// MQTT Configuration
const char* mqtt_server = "172.20.10.5";
const int mqtt_port = 1883;
const char* mqtt_topic = "sonde1";
const char* mqtt_client_id = "ESP32_Weather_Station";  // Fixed client ID for debugging

// Pin Configuration
#define BME_SDA 18
#define BME_SCL 19

// Data structure
struct WeatherData {
    float temperature_celsius;
    float pressure_hpa;
    float humidity_percent;
};

// Global objects
Adafruit_BME280 bme;
WiFiClient espClient;
PubSubClient client(espClient);

// Timing
const unsigned long SEND_INTERVAL = 5000;  // 5 seconds
unsigned long lastSendTime = 0;

// WiFi setup with timeout
void setup_wifi() {
    delay(10);
    Serial.println("\nConnecting to WiFi...");
    Serial.printf("SSID: %s\n", ssid);

    WiFi.mode(WIFI_STA);  // Explicitly set station mode
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
        Serial.print("Signal Strength (RSSI): ");
        Serial.println(WiFi.RSSI());
    } else {
        Serial.println("\nWiFi connection FAILED");
    }
}

// Print MQTT state for debugging
String getMQTTStateString(int state) {
    switch (state) {
        case -4: return "MQTT_CONNECTION_TIMEOUT";
        case -3: return "MQTT_CONNECTION_LOST";
        case -2: return "MQTT_CONNECT_FAILED";
        case -1: return "MQTT_DISCONNECTED";
        case 0: return "MQTT_CONNECTED";
        case 1: return "MQTT_CONNECT_BAD_PROTOCOL";
        case 2: return "MQTT_CONNECT_BAD_CLIENT_ID";
        case 3: return "MQTT_CONNECT_UNAVAILABLE";
        case 4: return "MQTT_CONNECT_BAD_CREDENTIALS";
        case 5: return "MQTT_CONNECT_UNAUTHORIZED";
        default: return "MQTT_UNKNOWN_STATE";
    }
}

// MQTT reconnection with debugging
void reconnect() {
    int attempts = 0;
    while (!client.connected() && attempts < 3) {  // Limit reconnection attempts
        Serial.println("\nAttempting MQTT connection...");
        Serial.printf("Broker: %s:%d\n", mqtt_server, mqtt_port);
        Serial.printf("Client ID: %s\n", mqtt_client_id);
        
        if (WiFi.status() != WL_CONNECTED) {
            Serial.println("WiFi not connected. Reconnecting WiFi first...");
            setup_wifi();
        }

        if (client.connect(mqtt_client_id)) {
            Serial.println("MQTT Connected successfully!");
            
            // Test message
            if (client.publish(mqtt_topic, "{\"status\":\"connected\"}")) {
                Serial.println("Test message sent successfully");
            } else {
                Serial.println("Failed to send test message");
            }
        } else {
            int state = client.state();
            Serial.print("MQTT connection failed, state: ");
            Serial.print(state);
            Serial.print(" (");
            Serial.print(getMQTTStateString(state));
            Serial.println(")");
            Serial.println("Retrying in 5 seconds...");
            delay(5000);
        }
        attempts++;
    }
}

WeatherData readSensorData() {
    WeatherData data;
    data.temperature_celsius = bme.readTemperature();
    data.pressure_hpa = bme.readPressure() / 100.0F;
    data.humidity_percent = bme.readHumidity();
    return data;
}

bool isValidData(const WeatherData& data) {
    return !isnan(data.temperature_celsius) && 
           !isnan(data.pressure_hpa) && 
           !isnan(data.humidity_percent) &&
           data.temperature_celsius > -40.0 && data.temperature_celsius < 85.0 &&
           data.pressure_hpa > 300.0 && data.pressure_hpa < 1100.0 &&
           data.humidity_percent >= 0.0 && data.humidity_percent <= 100.0;
}

void printMeasurements(const WeatherData& data) {
    Serial.println("\nCurrent Measurements:");
    Serial.printf("Temperature: %.1f °C\n", data.temperature_celsius);
    Serial.printf("Pressure: %.1f hPa\n", data.pressure_hpa);
    Serial.printf("Humidity: %.1f %%\n", data.humidity_percent);
}

bool sendData(const WeatherData& data) {
    StaticJsonDocument<200> doc;
    doc["temperature"] = data.temperature_celsius;
    doc["pressure"] = data.pressure_hpa;
    doc["humidity"] = data.humidity_percent;

    char jsonBuffer[200];
    serializeJson(doc, jsonBuffer);

    Serial.print("Attempting to send data: ");
    Serial.println(jsonBuffer);

    if (client.publish(mqtt_topic, jsonBuffer)) {
        Serial.println("Data sent successfully");
        return true;
    } else {
        Serial.println("Failed to send data");
        Serial.printf("MQTT State: %s\n", getMQTTStateString(client.state()).c_str());
        return false;
    }
}

void setup() {
    Serial.begin(115200);
    delay(2000);  // Give time for serial to initialize
    Serial.println("\nWeather Station - Sonde 1 Starting...");

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

    // Configure BME280
    bme.setSampling(Adafruit_BME280::MODE_NORMAL,
                    Adafruit_BME280::SAMPLING_X2,
                    Adafruit_BME280::SAMPLING_X16,
                    Adafruit_BME280::SAMPLING_X1,
                    Adafruit_BME280::FILTER_X16,
                    Adafruit_BME280::STANDBY_MS_500);

    // Setup WiFi
    setup_wifi();

    // Setup MQTT
    client.setServer(mqtt_server, mqtt_port);
    client.setBufferSize(512);  //a voir si pas besoin de l'augmenter
}

void loop() {
    // Check WiFi connection
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi connection lost. Reconnecting...");
        setup_wifi();
    }

    // Check MQTT connection
    if (!client.connected()) {
        Serial.println("MQTT disconnected. Reconnecting...");
        reconnect();
    }
    client.loop();

    // Read and send data at intervals
    if (millis() - lastSendTime > SEND_INTERVAL) {
        WeatherData data = readSensorData();
        
        if (isValidData(data)) {
            printMeasurements(data);
            if (sendData(data)) {
                lastSendTime = millis();
            }
        } else {
            Serial.println("Invalid sensor readings detected!");
        }
    }
}
