//=============================================
// File.......: saxeniotNilmReader.c
// Date.......: 2023-03-20
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

const char* host = "x.simuino.com";
const uint16_t port = 80;
String server_url = "/saxeniot_access.php";
String label = "kvv32_nilm";
int par  = 1;
int period = 5; // Sec

ESP8266WiFiMulti WiFiMulti;
int counter = 0;
String mac;

const byte led_v1            = 2; // D4 NodeMcu
const byte led_v2            = 5; // D1 NodeMcu,
const byte led_v3            = 4; // D2 NodeMcu
const byte led_v4            = 13; // D2 NodeMcu
const byte led_read          = 12; // D6 NodeMcu
const byte led_no_connection = 14; // D5 NodeMcu

int timeToCheckStatus    = 0;
unsigned long t1,t2,dt,ttemp;
float elpow               = 0.0;
int interrupt_counter     = 0;
int bounce_value          = 50; // minimum time between interrupts

//=============================================
void setup() {
//=============================================

  pinMode(LED_BUILTIN, OUTPUT);
  pinMode(led_v1, OUTPUT);
  pinMode(led_v2, OUTPUT);
  pinMode(led_v3, OUTPUT);
  pinMode(led_v4, OUTPUT);
  pinMode(led_read, OUTPUT);
  pinMode(led_no_connection, OUTPUT);
  
  digitalWrite(LED_BUILTIN,LOW);
  digitalWrite(led_v1,LOW);
  digitalWrite(led_v2,LOW);
  digitalWrite(led_v3,LOW);
  digitalWrite(led_v4,LOW);
  digitalWrite(led_read,LOW);
  digitalWrite(led_no_connection,LOW);

  digitalWrite(led_v1,HIGH);
  delay(500);
  digitalWrite(led_v2,HIGH);
  delay(500);
  digitalWrite(led_v3,HIGH);
  delay(500);
  digitalWrite(led_v4,HIGH);
  delay(500);
  digitalWrite(led_read,HIGH);
  delay(500);
  digitalWrite(led_no_connection,HIGH);
  delay(500);
  digitalWrite(led_v1,LOW);
  digitalWrite(led_v2,LOW);
  digitalWrite(led_v3,LOW);
  digitalWrite(led_v4,LOW);
  digitalWrite(led_read,LOW);
  //digitalWrite(led_no_connection,LOW);

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

  digitalWrite(led_no_connection,LOW);
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

  String url = server_url;
  url += "?par=";
  url += String(par);

  url += "&label=";
  url += label;

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
  char buf[120];
  int i = 0;
  int record = 0;
  while (client.available()) {
    char ch = static_cast<char>(client.read());
    Serial.print(ch);

    if (record == 1 && ch != ' ')
    {
      buf[i] = ch;
      i++;
    }

    if (ch=='+')
    {
      record = 1;
    }
    if (ch==' ' && record==1)
    {
      record = 0;
    }
    delay(5);
  
  }
  int value = atoi(buf);
  Serial.println(value);
  Serial.println("closing connection");
  client.stop();

  digitalWrite(led_v1,LOW);
  digitalWrite(led_v2,LOW);
  digitalWrite(led_v3,LOW);
  digitalWrite(led_v4,LOW);
  digitalWrite(led_no_connection,LOW);

  if (value >= 100 && value < 200)
  {
    digitalWrite(led_v1,HIGH);
  }
  if (value >= 200 && value < 300)
  {
    digitalWrite(led_v2,HIGH);
  }
  if (value >= 300 && value < 400)
  {
    digitalWrite(led_v1,HIGH);
    digitalWrite(led_v2,HIGH);
  }
  if (value >= 400 && value < 500)
  {
    digitalWrite(led_v3,HIGH);
  }
  if (value >= 500 && value < 600)
  {
    digitalWrite(led_v1,HIGH);
    digitalWrite(led_v3,HIGH);
  }
  if (value >= 600 && value < 700)
  {
    digitalWrite(led_v2,HIGH);
    digitalWrite(led_v3,HIGH);
  }
  if (value >= 700 && value < 800)
  {
    digitalWrite(led_v1,HIGH);
    digitalWrite(led_v2,HIGH);
    digitalWrite(led_v3,HIGH);
  }
  if (value >= 800 && value < 900)
  {
    digitalWrite(led_v4,HIGH);
  }
  if (value >= 900 && value < 1000)
  {
    digitalWrite(led_v1,HIGH);
    digitalWrite(led_v4,HIGH);
  }
  if (value >= 1000 && value < 1100)
  {
    digitalWrite(led_v2,HIGH);
    digitalWrite(led_v4,HIGH);
  }
  if (value >= 1100 && value < 1200)
  {
    digitalWrite(led_v1,HIGH);
    digitalWrite(led_v2,HIGH);
    digitalWrite(led_v4,HIGH);
  }
  if (value >= 1200 && value < 1300)
  {
    digitalWrite(led_v3,HIGH);
    digitalWrite(led_v4,HIGH);
  }
  if (value >= 1300 && value < 1400)
  {
    digitalWrite(led_v1,HIGH);
    digitalWrite(led_v3,HIGH);
    digitalWrite(led_v4,HIGH);
  }
  if (value >= 1400 && value < 1500)
  {
    digitalWrite(led_v2,HIGH);
    digitalWrite(led_v3,HIGH);
    digitalWrite(led_v4,HIGH);
  }
  if (value >= 1500 && value < 1600)
  {
    digitalWrite(led_v1,HIGH);
    digitalWrite(led_v2,HIGH);
    digitalWrite(led_v3,HIGH);
    digitalWrite(led_v4,HIGH);
  }
  if (value >= 1600)
  {
    digitalWrite(led_v1,HIGH);
    digitalWrite(led_v2,HIGH);
    digitalWrite(led_v3,HIGH);
    digitalWrite(led_v4,HIGH);
    digitalWrite(led_no_connection,HIGH);
  }


  digitalWrite(led_read,HIGH);
  delay(period*10);
  digitalWrite(led_read,LOW);


  delay(period*1000);
}
//=============================================
// End of File
//=============================================

