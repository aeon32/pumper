#include "pumpprotocol.h"
#include "consts.h"
#include "base64.h"

#define MAX_SENDED_STRUCTURE_SIZE 128


bool PumpProtocol::parseRequestHeader(const char * data, uint8_t dataSize, RequestHeader * hdrOut)
{
	bool res = false;
	uint8_t offset = 0;
	unsigned char * uData = (unsigned char *) data;
	unsigned char  type = 0;
	
	if (dataSize >= offset + 1)
	{
		type = uData[offset];
		
	};
	
	offset += 1;
	switch (type)
	{
		case NO_COMMAND_RESPONSE:
		case SWITCH_TO_MONITORING_MODE_RESPONSE:
		case SEND_INFO_REQUEST:
			res = true;
			hdrOut->type = type;
		break;
		
	};
	
	return res;
	
	
	
};


 void PumpProtocol::appendCheckCommandRequest(char * buffer,const char * imei)
 {
	 char aux[MAX_SENDED_STRUCTURE_SIZE];
	 *((unsigned char *)aux) = PUMP_COMMAND_CHECK;
	 
	 strcpy((char *)aux + 1, imei);
	 size_t fullLen = 1 + strlen(imei);
	 
	 b64_encode(aux, fullLen,  buffer);
 };
 
 #pragma pack(push, 1)
 struct MonitoringInfo {
	 uint8_t type;
	 uint32_t pressure;
	 uint8_t is_working;
	 uint8_t current_valve;
	 uint8_t current_step;
 };
 #pragma pack(pop) // disables the effect of #pragma pack from now o
 
 
 void my_htons(uint32_t * value)
 {
	 uint8_t aux;
	 uint8_t * convertedValue =  (uint8_t *) value;
	 aux = convertedValue[0];
	 convertedValue[0] = convertedValue[3];
	 convertedValue[3] = aux;
	 
	 aux = convertedValue[1];
	 convertedValue[1] = convertedValue[2];
	 convertedValue[2] = aux;
 }
 
 void PumpProtocol::appendSendInfoRequest(char * buffer,const char * imei)
 {
	 char aux[MAX_SENDED_STRUCTURE_SIZE];
	 MonitoringInfo * info = (MonitoringInfo *) &aux;
	 info->type =  COMMAND_CHECK_WITH_INFO_REQUEST;
	 info->pressure = 1000;
	 my_htons(&info->pressure);
	 info->is_working = 1;
	 info->current_step = 10;
	 info->current_valve = 5;
	 
	 strcpy(aux + sizeof(MonitoringInfo), imei);
	 size_t fullLen = sizeof(MonitoringInfo) + strlen(imei);
	 
	 b64_encode(aux, fullLen,  buffer);
	 
 }