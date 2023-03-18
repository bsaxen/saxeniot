//=============================================
// File.......: saxeniotTemperatureClient.c
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
String label = "kvv32_outdoor_temp";
//String label = "kvv32_heater_temp";
int period = 180; // Sec

ESP8266WiFiMulti WiFiMulti;
int counter = 0;
String mac;

//---------------------------------------------
#include <OneWire.h>
#include <DallasTemperature.h>
#define ONE_WIRE_BUS 5 // Pin for connecting one wire sensor
#define TEMPERATURE_PRECISION 12

OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensor(&oneWire);
DeviceAddress device[10];
int nsensors = 0;
//---------------------------------------------

//=============================================
void setUpTemperatureSensors()
//=============================================
{
    pinMode(ONE_WIRE_BUS, INPUT);
    sensor.begin();
    nsensors = sensor.getDeviceCount();
    if(nsensors > 0)
    {
        for(int i=0;i<nsensors;i++)
        {
            sensor.getAddress(device[i], i);
            sensor.setResolution(device[i], TEMPERATURE_PRECISION);
        }
    }
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

  setUpTemperatureSensors();
  int conf_sensors = nsensors;

  delay(500);
}

//=============================================
void loop() {
//=============================================
  float temps[10];
  int i;

  //Retrieve one or more temperature values
  sensor.requestTemperatures();
  //Loop through results and publish
  for(int i=0;i<nsensors;i++)
  {
     //float temperature = sensor.getTempCByIndex(i);
      float temperature = sensor.getTempC(device[i]);
      if (temperature > -100) // filter out bad values , i.e. -127
      {
        temps[i] = temperature;
        Serial.println(temperature);
      }
  }

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
  url += String(nsensors);
  
  if (nsensors > 0)
  {
    for (int i=1;i<=nsensors;i++)
    {
        url += "&p"+String(i)+"=";
        url += String(temps[i-1]);
    }
  }
  
  Serial.println(url);
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
