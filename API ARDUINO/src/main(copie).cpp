#include <Arduino.h>
#include <esp8266_undocumented.h>
#include <Adafruit_BME280.h>
#include <string.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266httpUpdate.h>
#include <ESP8266WebServer.h>

const char* server = "172.20.10.5";
const char* ssid = "1883";
const char* password = "???"; //mdp pour connexion serveur

// Déclaration des floats verifier sur internet c'est quoi
float temperature = 0;
float humidite = 0;
float pression = 0;

// Déclaration de l'objet BME280
Adafruit_BME280 bme;
// Déclaration du serveur php a faire corriger
ESP8266WebServer server(80);

// processus de connexion wifi
void connexionWifi() 
{
    WiFi.enableSTA(true);
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
                delay(500);
                Serial.print(".");
        }
    Serial.println("WiFi connected");
}

// initialisation du capteur bme280
void initSensor()
{
    bool status = bme.begin(0x76);
    if (!status) {
        Serial.print1n("il est ou ce putin de capteur !");
    while (1);
    }
}

void handleRoot() {
    String html = "<html><body>";
    html += "<h1>Station Meteo</h1>";
    html += "<h3>Température : " + String(temperature) + " °C</h3>"; //faire corespondre les balises avec le html
    html += "<h3>Humidité : " + String(humidite) + " %</h3>"; //faire corespondre les balises avec le html
    html += "<h3>Pression : " + String(pression) + " hPa</h3>"; //faire corespondre les balises avec le html
    html += "</body></html>";
    server.send(200, "text/html", html);
}

void initWebServer() {
    server.on("/", handleRoot);
    server.begin();
    Serial.println("Serveur web démarré");
}

void sendDataToDatabase() {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        String url = String("http://") + server + "/storeData.php"; // Remplacez avec l'URL de votre API ou script
        http.begin(url);

        String payload = "temperature=" + String(temperature) + 
                        "&humidite=" + String(humidite) + 
                        "&pression=" + String(pression);

        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        int httpCode = http.POST(payload);

        if (httpCode > 0) {
            Serial.println("Données envoyées avec succès !");
        } else {
            Serial.println("Erreur lors de l'envoi des données");
        }

        http.end();
    } else {
        Serial.println("Pas de connexion WiFi pour envoyer les données !");
    }
}


void loop() {
    // Lire les données du capteur
    temperature = bme.readTemperature();
    humidite = bme.readHumidity();
    pression = bme.readPressure() / 100.0F;

    // Envoyer les données à la base de données toutes les 10 secondes
    static unsigned long lastSend = 0;
    if (millis() - lastSend > 10000) {
        sendDataToDatabase();
        lastSend = millis();
    }

    // Gérer les requêtes du serveur web
    server.handleClient();
}

void setup() {
    Serial.begin(115200);
    connexionWifi();
    initSensor();
    initWebServer();
}


