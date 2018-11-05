/*
 * pumpprotocol.h
 *
 * Created: 11/5/2018 10:29:17 AM
 *  Author: 3ema
 */ 


#ifndef PUMPPROTOCOL_H_
#define PUMPPROTOCOL_H_

#include <Arduino.h>

struct RequestHeader
{
	unsigned char type;           //one of PumpProtocol::MessageType value
	
};

class PumpProtocol 
{
	public:
      enum MESSAGE_TYPE
      {
	    PUMP_COMMAND_CHECK = 0x30,
	    NO_COMMAND_RESPONSE = 0x31,   //nothing to do
	    GET_INFO_RESPONSE = 0x32,    //controller must return information
	    SEND_INFO_REQUEST = 0x33,    //send info
	    SWITCH_TO_MONITORING_MODE_RESPONSE = 0x34, //
	    COMMAND_CHECK_WITH_INFO_REQUEST=0x35 //
     };	
	 bool parseRequestHeader(const char * data, uint8_t dataSize, RequestHeader * hdrOut);
	 
	 //append check_info_request to buffer
	 void appendCheckCommandRequest(char * buffer,const char * imei);
	 
	 //put send_info_request to buffer
	 void appendSendInfoRequest(char * buffer,const char * imei);
};





#endif /* PUMPPROTOCOL_H_ */