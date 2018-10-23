<?php
 $nick = "admin";
 $password = "admin";
 $md5 = md5($nick . $password);

 print($md5);

?>