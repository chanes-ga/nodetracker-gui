<?php
include("dbConnect.inc");
include("crypt.inc");


$r=verifyKey($key);
if ($r!=0){
	$keyvalid=1;
	session_register("key");
	session_register("keyvalid");
	$url="cryptScreen.php";
	header("Location: $url"); 
	
}else{
	print "Invalid encryption key.<br><br>
		Please note that your key is stored as an MD5 hash in our system.  <br>In other words, we don't know what it is.";
}


?>
