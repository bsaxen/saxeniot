//=============================================
// File.......: saxeniotNilmReader.c
// Date.......: 2023-03-20
// Author.....: Benny Saxen
// Description: 
//=============================================
#include <WiFi.h>
#include "SSD1306.h" // alias for `#include "SSD1306Wire.h"'

#ifndef STASSID
//#define STASSID "bridge"
//#define STAPSK  "1234"
#define STASSID "NABTON"
#define STAPSK  "a1b2c3d4e5f6g7"
#endif

const char* ssid     = STASSID;
const char* password = STAPSK;

const char* host = "rdf.simuino.com";
const uint16_t port = 80;

int par  = 1;
int period = 5; // Sec

int ix=2;
int iy=1;

int counter = 0;
String mac;


int timeToCheckStatus    = 0;
unsigned long t1,t2,dt,ttemp;
float elpow               = 0.0;
int interrupt_counter     = 0;
int bounce_value          = 50; // minimum time between interrupts

// Initialize the OLED display using Wire library
SSD1306  display(0x3c, 5, 4);
// SSD1306 display(0x3c, SDA, SCL);


//=============================================
void setup() {
//=============================================
  display.init();
  display.flipScreenVertically();
  display.setFont(ArialMT_Plain_16);

  

  Serial.begin(115200);

  // We start by connecting to a WiFi network
  WiFi.begin(ssid, password);

  Serial.println();
  Serial.println();
  Serial.print("Wait for WiFi... ");

  while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }

  mac = WiFi.macAddress();


  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());

  Serial.println("MAC address: ");
  Serial.println(mac);
  mac.replace(":","-");


  delay(500);
}

//=============================================
void loop() {
//=============================================
 
  ix = ix + 1;
  iy = iy + 1;
  if (ix > 100)ix = 2;
  if (iy > 40)iy = 1;
  //Serial.print("connecting to ");
  //Serial.print(host);
  //Serial.print(':');
  //Serial.println(port);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;

  if (!client.connect(host, port)) {
    //Serial.println("connection failed");
    //Serial.println("wait 5 sec...");
    delay(10000);
    return;
  }

  //===================================================
  String url = "/saxeniot_access.php?power=1&label=kvv32_nilm";
  //Serial.println(url);
 
  // This will send the request to the server
   client.print(String("GET ") + url + " HTTP/1.1\r\n" +
             "Host: " + host + "\r\n" +
             "Connection: close\r\n\r\n");
             
  unsigned long timeout = millis();
  while (client.available() == 0) {
     if (millis() - timeout > 5000) {
        //Serial.println(">>> Client Timeout !");
        client.stop();
        return;
     }
     delay(5);
  }
  //Serial.println("receiving from remote server");
 
  String result ;
  
  while(client.available()) {
        result = client.readStringUntil('\r');
        //Serial.print(result);
    }
  
  display.clear();
  display.setColor(WHITE);
  //display.setTextAlignment(TEXT_ALIGN_CENTER);
  display.setTextAlignment(TEXT_ALIGN_LEFT);
  display.drawString(ix, iy, result);

  display.setFont(ArialMT_Plain_16);
  display.display();

  //Serial.println("closing connection");
  client.stop();

  delay(period*1000);
}
//=============================================
// End of File
//=============================================

