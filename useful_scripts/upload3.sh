#!/bin/bash

currentdir=$(cd `dirname $0` && pwd)

HOST="ftp.f0245646.xsph.ru"
USER="f0245646"
PASS="dunuehbibi"
FTPURL="ftp://$USER:$PASS@$HOST"
LCD="$currentdir/../web"
RCD="domains/f0245646.xsph.ru/public_html/"
#DELETE="--delete"
lftp -c "set ftp:list-options -a;\
set ftp:ssl-allow no;
open '$FTPURL';\
lcd $LCD;\
cd $RCD;\
mirror --reverse \
       $DELETE \
       --verbose \
       --exclude-glob .idea/\
       --exclude-glob configuration.ini"


