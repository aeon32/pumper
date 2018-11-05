/*
 * base64.h
 *
 * Created: 11/5/2018 12:55:20 PM
 *  Author: 3ema
 */ 


#ifndef BASE64_H_
#define BASE64_H_

// in : buffer of "raw" binary to be encoded.
// in_len : number of bytes to be encoded.
// out : pointer to buffer with enough memory, user is responsible for memory allocation, receives null-terminated string
// returns size of output including null byte
int b64_encode(const char *in, int len, char *encoded);






#endif /* BASE64_H_ */