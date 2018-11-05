#!/bin/bash

currentdir=$(cd `dirname $0` && pwd)

HOST="ftpupload.net"
USER="fhiox_22948493"
PASS="demission"
FTPURL="ftp://$USER:$PASS@$HOST"
LCD="$currentdir/../web"
RCD="htdocs"
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


