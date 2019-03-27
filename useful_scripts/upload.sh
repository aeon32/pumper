#!/bin/bash

currentdir=$(cd `dirname $0` && pwd)

HOST="files.000webhost.com"
USER="aeration2"
PASS="25eTULGNmMQw"
FTPURL="ftp://$USER:$PASS@$HOST"
LCD="$currentdir/../web"
RCD="public_html"
#DELETE="--delete"
lftp -c "set ftp:list-options -a;\
open '$FTPURL';\
lcd $LCD;\
cd $RCD;\
mirror --reverse \
       $DELETE \
       --verbose \
       --exclude-glob .idea/\
       --exclude-glob configuration.ini"


