/*Begining of Auto generated code by Atmel studio */
#include <Arduino.h>

/*End of auto generated code by Atmel studio */

#include <SoftwareSerial.h>

#include <DTE.h>
#include <GPRS.h>
#include <GSM.h>
#include <HTTP.h>
#include <IP.h>
#include <URC.h>
//Beginning of Auto generated function prototypes by Atmel Studio
//End of Auto generated function prototypes by Atmel Studio



#define PINPOWER 9
#define DEBUG false
#define APN "internet.tele2.ru"
#define URL "http://aeration.000webhostapp.com/controlserver/" 
#define USERNAME ""
#define PASSWORD ""



const char * imei = NULL;
char fullUrl[256];
//SoftwareSerial SSerial(10, 11);

DTE dte(Serial1, PINPOWER, DEBUG);
GSM gsm(dte);
GPRS gprs(dte);
IP ip(dte, gprs);
HTTP http(dte, ip);

void setup() {
  Serial.begin(115200);
  Serial1.begin(115200);  // The fastest and yet safe speed for SoftwareSerial

  Serial.println("Power Reset\r\n");
  dte.powerReset();
  ip.setConnectionParamGprs(APN, USERNAME, PASSWORD);
  
  
  while (!imei)
  {
	  imei = dte.getProductSerialNumberIdentification();
	  if (!imei)
	  {
		  delay(5000);
	  }
	  
  }
  
  strcpy(fullUrl,URL);
  strcat(fullUrl, "?MGFiY2Q=");
}

void loop() {
  bool ok;
  if (!dte.AT())
    dte.powerReset();

  if (ip.openConnection()) {  // Open Connection, if it's already connected it just return true
    Serial.print("Network Registration: ");
    Serial.println(gsm.getNetworkRegistration().status);
    Serial.print("GPRS Attached: ");
    Serial.println(gprs.isAttached());
    Serial.println("Connected!");
    Serial.println();
    unsigned long timeout = 30;  // The minimum HTTP Timeout setting
    http.initialize(timeout);    // First parameter is for enabling SSL
	Serial.println("Sending");
	Serial.println(fullUrl);
    ok = http.action("GET", fullUrl);
	Serial.println(ok ? "sent" : "failed");
    unsigned long t = millis();
    while ((millis() - t < (timeout + 1) * 1000) && !Urc.httpAction.updated && dte.AT())
      dte.clearReceivedBuffer();  // This is necessary, so URC can be captured
    if (Urc.httpAction.updated && Urc.httpAction.statusCode == 200) {
      Serial.println("Data Received: ");

      for (unsigned long i = 0; i < Urc.httpAction.dataLength;) {
        char response[101];  // Plus 1 for terminate null char
        http.readDataReceived(response, sizeof(response) - 1, i);
        i += sizeof(response) - 1;
        Serial.print(response);
      }
      Serial.println();

      /**
       * Code below can also be used, but it take so much memory.
       * Depend on Urc.httpAction.dataLength, but it is not necessary to read
       * all Server Response.
       */
      //char response[Urc.httpAction.dataLength + 1];
      //http.readDataReceived(response, Urc.httpAction.dataLength);
      //Serial.print(response);
      //Serial.println();
    } else {
      Serial.println("Failed");
      if (Urc.httpAction.updated) {
        Serial.print("Status Code: ");
        Serial.println(Urc.httpAction.statusCode);
        if (Urc.httpAction.statusCode == 601)
          Serial.print("Try to add https:// for SSL site or http:// for non-SSL site");
      }
    }
    http.terminate();
    Serial.println("Done!");
	/*
    Serial.println("Press any key to see next connection behave...");
    Serial.println("*It's already connected, so it's faster. :)");
    while (Serial.available() == 0)
      ;
    while (Serial.available() > 0) {
      Serial.read();
      delay(50);
    }
	*/
  } else {
    Serial.print("Network Registration: ");
    Serial.println(gsm.getNetworkRegistration().status);
    Serial.print("GPRS Attached: ");
    Serial.println(gprs.isAttached());
    Serial.println("Wait to connect...");
    Serial.println();
  }
  delay(100);  //It's just for debugging, no need to delay actually
}
