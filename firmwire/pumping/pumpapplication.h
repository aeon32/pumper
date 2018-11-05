/*
 * pumpapplication.h
 *
 * Created: 11/5/2018 10:37:17 AM
 *  Author: 3ema
 */ 


#ifndef PUMPAPPLICATION_H_
#define PUMPAPPLICATION_H_

#include "pumpprotocol.h"

#include <Arduino.h>
#include <SoftwareSerial.h>

#include <DTE.h>
#include <GPRS.h>
#include <GSM.h>
#include <HTTP.h>
#include <IP.h>
#include <URC.h>


struct ConnectionSettings
{
	const char * apn;
	const char * url;
	const char * password;
	const char * userName;

	
};

class PumpApplication
{
	private:
	  DTE dte;
	  GSM gsm;
	  GPRS gprs;
	  IP ip;
	  HTTP http;
	  
	  HardwareSerial &hardwareSerial;
	  PumpProtocol pumpProtocol;
	  
	  bool fullMonitoringInfoFlag;
	  unsigned long lastRequestInfoTime;
	  
	  
	  const char * imei;
	  char fullUrl[256];
	  
	  uint8_t permamentPartUrlLen;
	  
	  void handleRequest(const char * data, size_t dataSize);

	public:
	  PumpApplication(HardwareSerial &hardwareSerial, int pinPower, bool debug);
	  void setup(const ConnectionSettings & connectionSettings );
	  void loop();
	
	
	
};




#endif /* PUMPAPPLICATION_H_ */