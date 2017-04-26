<?php
include ("crypt.inc");
$key=$HTTP_SESSION_VARS["key"];
$cmd="/sqldbs/nodetracker/scripts/cryptAll.pl -u $PHP_AUTH_USER -p $PHP_AUTH_PW -d $db -k $key -x $direction";
#print $cmd;
$r=$HTTP_SESSION_VARS["keyvalid"];
if($r!=0){	
	if (($direction==1)){
		#data not encrypted and we are asked to encrypt		
		print "Encrypting now...<Br><pre>";
		system($cmd);

	}else {
		#data is encrypted and we are asked to decrypt
		print "Decrypting now....<br><pre>";
		system($cmd);
	}
}
?>
