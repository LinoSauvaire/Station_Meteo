#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
#include <LiquidCrystal_I2C.h>

// WiFi Connection
const char* ssid = "iPhone";
const char* password = "05062001";

// API Configuration
const char* api_url = "http://172.20.10.5/weather_api/api.php";

//Declaration ecran lcd
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Pin Configuration
#define BME_SDA 18
#define BME_SCL 19
#define LCD_SDA 18
#define LCD_SCL 19

// Data structure
struct WeatherData {
    float temperature;
    float pressure;
    float humidity;
};

// Global object
Adafruit_BME280 bme;

// Interval lecture/envoi
const unsigned long SEND_INTERVAL = 5000;  // 5 seconds
unsigned long lastSendTime = 0;

// WiFi setup with timeout
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

WeatherData readSensorData() {
    WeatherData data;
    data.temperature = bme.readTemperature();
    data.pressure = bme.readPressure() / 100.0F;
    data.humidity = bme.readHumidity();
    return data;
}

bool isValidData(const WeatherData& data) {
    return !isnan(data.temperature) && 
           !isnan(data.pressure) && 
           !isnan(data.humidity) &&
           data.temperature > -40.0 && data.temperature < 85.0 &&
           data.pressure > 300.0 && data.pressure < 1100.0 &&
           data.humidity >= 0.0 && data.humidity <= 100.0;
}

void printMeasurements(const WeatherData& data) {
    Serial.println("\nCurrent Measurements:");
    Serial.printf("Temperature: %.1f °C\n", data.temperature);
    Serial.printf("Pressure: %.1f hPa\n", data.pressure);
    Serial.printf("Humidity: %.1f %%\n", data.humidity);
}

bool sendDataToAPI(const WeatherData& data) {
    if(WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi not connected");
        return false;
    }

    HTTPClient http;
    http.begin(api_url);
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<200> doc;
    doc["temperature"] = data.temperature;
    doc["pressure"] = data.pressure;
    doc["humidity"] = data.humidity;

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

void setup() {
    Serial.begin(115200);
    delay(2000);
    Serial.println("\nWeather Station Starting...");

    // Initialize I2C
    Wire.begin();
    Serial.println("I2C initialized");

    //Initialisation LCD
    lcd.init();  // Initialisation de l'écran LCD
    lcd.backlight();
    lcd.setCursor(0, 0);      
    lcd.print("Bienvenue !");
    
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
}

void loop() {
    // Check WiFi connection
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi connection lost. Reconnecting...");
        setup_wifi();
    }

    // Read and send data at intervals
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
