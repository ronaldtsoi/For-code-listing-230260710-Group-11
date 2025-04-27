#include <Arduino.h>
#include <BLEDevice.h>
#include <BLEUtils.h>
#include <BLEServer.h>
#include <BLE2902.h>

#define SERVICE_UUID        "a64c8c67-9358-460c-a579-004d54d56cae"
#define CHARACTERISTIC_UUID "9f854be0-e6f5-45e5-bf4c-eb10ef4180fc"

BLEServer* pServer = nullptr;
BLECharacteristic* pCharacteristic = nullptr;
bool deviceConnected = false;
int redPin= 5;  // Red Pin of the RGB led
int greenPin = 18; // Green Pin of the RGB led
int  bluePin = 19; // Blue Pin of the RGB led
int magnetPin = 17; // Detect Magnet (Digital Pin)
int distancePin = 21; // Detect Human (Digital Pin)
uint8_t isWorn = 0; // 0 = Not Worn, 1 = Worn


// BLE Server Callbacks
class MyServerCallbacks : public BLEServerCallbacks {
    void onConnect(BLEServer* pServer) {
        deviceConnected = true;
        Serial.println("Device connected");
    }

    void onDisconnect(BLEServer* pServer) {
        deviceConnected = false;
        Serial.println("Device disconnected");
        pServer->getAdvertising()->start(); // Restart advertising
    }
};

void setupBLE() {
  BLEDevice::init("FYP Safety Plus");
  pServer = BLEDevice::createServer();
  pServer->setCallbacks(new MyServerCallbacks());

  BLEService *pService = pServer->createService(SERVICE_UUID);

  pCharacteristic = pService->createCharacteristic(
      CHARACTERISTIC_UUID,
      BLECharacteristic::PROPERTY_READ |
      BLECharacteristic::PROPERTY_NOTIFY
  );

  // Add CCCD Descriptor (Required for Notifications)
  pCharacteristic->addDescriptor(new BLE2902());

  pCharacteristic->setValue(&isWorn, 1); // Initialize with 0
  pService->start();

  // Start BLE Advertising
  BLEAdvertising *pAdvertising = BLEDevice::getAdvertising();
  pAdvertising->addServiceUUID(SERVICE_UUID);
  pServer->getAdvertising()->start();

  Serial.println("BLE Magnet Detection Started!");
}


void setColor(int redValue, int greenValue,  int blueValue) {
  analogWrite(redPin, redValue);
  analogWrite(greenPin,  greenValue);
  analogWrite(bluePin, blueValue);
}

void changeRGB(int value){
  switch(value){
    case 1:
      setColor(0, 0, 50); // Blue Color
      break;
    case 2:
      setColor(0, 50, 0); // Green Color
      break;
    default:
      setColor(50, 0, 0); // Red Color
  }
}

void setup() {
    Serial.begin(9600);
    Serial.println("Starting BLE...");
    setupBLE();

    pinMode(redPin,  OUTPUT);              
    pinMode(greenPin, OUTPUT);
    pinMode(bluePin, OUTPUT);
    pinMode(magnetPin, INPUT);
    pinMode(distancePin, INPUT);
}

void loop() {
    int rgbValue = 0;
    // Read magnet sensor (digital)
    bool isMagnetDetected = (digitalRead(magnetPin) == HIGH);
    bool isHumanDetected = (digitalRead(distancePin) == HIGH);
    Serial.print("Magnet Status: ");
    Serial.println(isMagnetDetected ? "No Magnet" : "Magnet Detected");
    Serial.print("Human Status: ");
    Serial.println(isHumanDetected ? "No Human" : "Human Detected");

    // Send data via BLE if connected
    if (deviceConnected) {
      if (isMagnetDetected || isHumanDetected) {
        isWorn = 0; // Not Worn
        rgbValue = 1; // Blue
      } else {
        isWorn = 1; // Worn
        rgbValue = 2; // Green
      }
      
      pCharacteristic->setValue(&isWorn, 1); // Send uint8_t value
      pCharacteristic->notify(); // Notify the connected device
      Serial.print("Worn Status: ");
      Serial.println(isWorn == 1 ? "Worn" : "Not Worn");
    }
    changeRGB(rgbValue);
    delay(1000); // Send updates every 500ms
}