<?php
include("dbConnect.inc");



$sql="update EncryptionStatus set $f=1";
$q=mysql_query($sql);


print "The request for the specified action has been succesfully scheduled";
