#!/bin/bash
user=pump_user
password=12345678
dbname=pump

mysqldump --user=$user --password=$password -R    $dbname > dump