#include "pumpapplication.h"
#include "consts.h"



#define SENDING_MONITORING_INFO_OFF_GUARD_INTERVAL 20000

PumpApplication::PumpApplication(HardwareSerial &hardwareSerial, int pinPower, bool debug)
  :dte(hardwareSerial, pinPower, debug),
   gsm(dte),
   gprs(dte),
   ip(dte, gprs),
   http(dte, ip),
   hardwareSerial(hardwareSerial),
   imei(NULL),
   fullMonitoringInfoFlag(false)
{
	
	
};


void PumpApplication::setup(const ConnectionSettings & connectionSettings)
{
	hardwareSerial.begin(115200);  // The fastest and yet safe speed for SoftwareSerial
	
	
	Serial.println("Power Reset\r\n");
	dte.powerReset();
	ip.setConnectionParamGprs(connectionSettings.apn, connectionSettings.userName, connectionSettings.password);
	
	while (!imei)
	{
		imei = dte.getProductSerialNumberIdentification();
		if (!imei)
		{
			delay(5000);
		}
		
	}
	Serial.println(imei);
	
	strcpy(fullUrl,connectionSettings.url);
	permamentPartUrlLen = strlen(fullUrl);
	//strcat(fullUrl, "MGFiY2Q=");
	
	
};

void PumpApplication::loop()
{

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
	
	
	if (this->fullMonitoringInfoFlag)
		pumpProtocol.appendSendInfoRequest(fullUrl + permamentPartUrlLen, this->imei);
	else
		pumpProtocol.appendCheckCommandRequest(fullUrl + permamentPartUrlLen, this->imei);
	
	
	Serial.println(fullUrl);
    ok = http.action("GET", fullUrl);
	Serial.println(ok ? "sent" : "failed");
    unsigned long t = millis();
    while ((millis() - t < (timeout + 1) * 1000) && !Urc.httpAction.updated && dte.AT())
      dte.clearReceivedBuffer();  // This is necessary, so URC can be captured
    if (Urc.httpAction.updated && Urc.httpAction.statusCode == 200) {
      Serial.println("Data Received: ");
	  
	  char response[MAX_RECIEVED_DATA];  
	  size_t dataReaded = 0;

      bool hasReaded = http.readDataReceived(response, sizeof(response), dataReaded);
	  if (hasReaded)
		this->handleRequest(response, min(Urc.httpAction.dataLength, sizeof(response)));
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
  unsigned long currentTime = millis();
  if (fullMonitoringInfoFlag && (currentTime - this->lastRequestInfoTime > SENDING_MONITORING_INFO_OFF_GUARD_INTERVAL ))
	fullMonitoringInfoFlag = false;
  
  delay(fullMonitoringInfoFlag ? 250 : 5000);  //It's just for debugging, no need to delay actually	
	
	
};

void PumpApplication::handleRequest(const char * data, size_t dataSize)
{
	Serial.println("HandleRequest");
	RequestHeader header;
	if (pumpProtocol.parseRequestHeader(data, dataSize, &header))
	{
		switch(header.type)
		{
			case PumpProtocol::SWITCH_TO_MONITORING_MODE_RESPONSE:
			case PumpProtocol::GET_INFO_RESPONSE:
				fullMonitoringInfoFlag = true;
				this->lastRequestInfoTime = millis();
			break;
		}

	}
	
};