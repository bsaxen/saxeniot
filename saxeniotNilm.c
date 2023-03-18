//=============================================
// File.......: saxeniotNilm.c
// Date.......: 2023-03-18
// Author.....: Benny Saxen
// Description: 
//=============================================

#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>

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
String url = "/saxeniot_server.php";
String label = "kvv32_nilm";
int period = 5; // Sec

ESP8266WiFiMulti WiFiMulti;
int counter = 0;
String mac;

const byte interrupt_pin = 5; // D1 NodeMcu
const byte led_pin       = 4; // D2 NodeMcu
int timeToCheckStatus    = 0;
unsigned long t1,t2,dt,ttemp;
float elpow               = 0.0;
int interrupt_counter     = 0;
int bounce_value          = 50; // minimum time between interrupts

int conf_kwh_pulses  = 1000; //1000 pulses/kWh
//===============================================================
// Interrupt function for measuring the time between pulses and number of pulses
// Always stored in RAM
void ICACHE_RAM_ATTR measure(){
//===============================================================
    //digitalWrite(led_pin,HIGH);
    ttemp = t1;
    t2 = t1;
    t1 = millis();
    dt = t1 - t2;
    if (dt < bounce_value)
    {
        t1 = ttemp;
        digitalWrite(led_pin,LOW);
        return;
    }
    elpow = 3600.*1000.*1000./(conf_kwh_pulses*dt);
    interrupt_counter++;
    //digitalWrite(led_pin,LOW);
}
//=============================================
void setup() {
//=============================================
  Serial.begin(9600);

  // We start by connecting to a WiFi network
  WiFi.mode(WIFI_STA);
  WiFiMulti.addAP(ssid, password);

  Serial.println();
  Serial.println();
  Serial.print("Wait for WiFi... ");

  while (WiFiMulti.run() != WL_CONNECTED) {
    Serial.print(".");
    delay(500);
  }

  mac = WiFi.macAddress();

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());

  Serial.println("MAC address: ");
  Serial.println(mac);
  mac.replace(":","-");


  
  bounce_value = 36000./conf_kwh_pulses; // based on max power = 100 000 Watt

  pinMode(interrupt_pin, INPUT_PULLUP);
  pinMode(led_pin, OUTPUT);
  digitalWrite(led_pin,LOW);

  attachInterrupt(interrupt_pin, measure, FALLING);


  delay(500);
}

//=============================================
void loop() {
//=============================================
  counter += 1;
  if (counter > 999999)
  {
    counter = 1;
  }
  
  Serial.print("connecting to ");
  Serial.print(host);
  Serial.print(':');
  Serial.println(port);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;

  if (!client.connect(host, port)) {
    Serial.println("connection failed");
    Serial.println("wait 5 sec...");
    delay(10000);
    return;
  }

  String url = "/device_server.php";
  url += "?id=";
  url += mac;
  
  url += "&category=";
  url += "esp8266";

  url += "&label=";
  url += label;

  url += "&counter=";
  url += String(counter);


  url += "&period=";
  url += String(period);

  url += "&tot=";
  url += String(2);
  
  url += "&p1=";
  url += String(elpow);

  url += "&p2=";
  url += String(interrupt_counter);
  
  Serial.println(url);
  interrupt_counter = 0;
  // This will send the request to the server
   client.print(String("GET ") + url + " HTTP/1.1\r\n" +
             "Host: " + host + "\r\n" +
             "Connection: close\r\n\r\n");
  unsigned long timeout = millis();
  while (client.available() == 0) {
     if (millis() - timeout > 5000) {
        Serial.println(">>> Client Timeout !");
        client.stop();
        return;
     }
     delay(5);
  }
  Serial.println("receiving from remote server");
  while (client.available()) {
    char ch = static_cast<char>(client.read());
    Serial.print(ch);
    delay(5);
  }
  Serial.println("closing connection");
  client.stop();

  delay(period*1000);
}
//=============================================
// End of File
//=============================================
