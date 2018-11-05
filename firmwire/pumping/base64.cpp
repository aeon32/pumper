/*
 * base64.cpp
 *
 * Created: 11/5/2018 12:55:40 PM
 *  Author: 3ema
 */ 
#include "base64.h"


//Base64 char table - used internally for encoding
static const char basis_64[] =
"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";



int b64_encode(const char *in, int len, char *encoded)
{
	int i;
	char *p;

	p = encoded;
	for (i = 0; i < len - 2; i += 3) {
		*p++ = basis_64[(in[i] >> 2) & 0x3F];
		*p++ = basis_64[((in[i] & 0x3) << 4) |
		((int) (in[i + 1] & 0xF0) >> 4)];
		*p++ = basis_64[((in[i + 1] & 0xF) << 2) |
		((int) (in[i + 2] & 0xC0) >> 6)];
		*p++ = basis_64[in[i + 2] & 0x3F];
	}
	if (i < len) {
		*p++ = basis_64[(in[i] >> 2) & 0x3F];
		if (i == (len - 1)) {
			*p++ = basis_64[((in[i] & 0x3) << 4)];
			*p++ = '=';
		}
		else {
			*p++ = basis_64[((in[i] & 0x3) << 4) |
			((int) (in[i + 1] & 0xF0) >> 4)];
			*p++ = basis_64[((in[i + 1] & 0xF) << 2)];
		}
		*p++ = '=';
	}

	*p++ = '\0';
	return p - encoded;
}

