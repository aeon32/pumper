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

#include "pumpapplication.h"


#define PINPOWER 9
#define DEBUG false
#define URL "http://f0245646.xsph.ru/controlserver/?" 
#define APN "internet.tele2.ru"
#define USERNAME ""
#define PASSWORD ""





//SoftwareSerial SSerial(10, 11);

PumpApplication application(Serial1, PINPOWER, DEBUG);

void setup() {
  Serial.begin(115200);
  
  struct ConnectionSettings settings;
  settings.apn = APN;
  settings.url = URL;
  settings.userName = USERNAME;
  settings.password = PASSWORD;
  application.setup(settings);


  
}

void loop() {
	application.loop();
}
