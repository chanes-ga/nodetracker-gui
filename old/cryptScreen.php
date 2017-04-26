<?php
include("dbConnect.inc");

$keyvalid=$HTTP_SESSION_VARS["keyvalid"];

if ($keyvalid==0){
	print "Establish encryption key for this session: <form method=post action=verifyKey.php><input type=password name=key>
		<input type=submit value=Verify></form><hr>";
}else{
	print "Encryption key has been established for this session.<br><Br>";
}

$sql="select state from EncryptionStatus";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);


if ($keyvalid==1){

	if ($r->state==1){
		$message="Your database is encrypted.  You need to decrypt it.";
		$direction=0;
		$submit="Decrypt";
	}else{
		$message="You can encrypt your database here.";
		$submit="Encrypt";
		$direction=1;
		$html="<form method=post action=changeKey.php><hr>You can change your encryption key here.<br><br>
			<table border=0>
			<th>Old Key</th><th><input type=password name=oldkey></th></tr>
			<th>New Key<br><font size=-2>Enter 2x</font></th><th><input type=password name=newkey1><br><input type=password 
name=newkey2></th></tr>
			</table><br><input type=submit value='Change Key'></form>";
	}

	print "$message<br><form method=post action=crypt.php>
		<input type=hidden name=direction value=$direction><input type=submit value=$submit>
		</form>$html
		";
}


?> <br><br>
WARNING: This feature is under development do not attempt to use!
