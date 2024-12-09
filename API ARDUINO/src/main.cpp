//Librairie pour le capteur bme280
#include <Arduino.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>

//Envoi des donnée a mariaDB
#include <WiFi.h>
#include <HTTPClient.h>

//L'API REST fournit des données au format JSON pour l'interface web.
#include <WebServer.h>














// put function declarations here:
int myFunction(int, int);

void setup() {
  // put your setup code here, to run once:
  int result = myFunction(2, 3);
}

void loop() {
  // put your main code here, to run repeatedly:
}

// put function definitions here:
int myFunction(int x, int y) {
  return x + y;
}