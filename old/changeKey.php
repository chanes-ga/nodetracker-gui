<pre>
<?php
	include("dbConnect.inc");
	include("crypt.inc");
	$p=0;
	if($oldkey!=$HTTP_SESSION_VARS["key"]){
		$p=1;
		print "You do not provide your old key correctly!<Br>";
	}
	if($newkey1!=$newkey2){
		print "Values do not match for new key!<Br>";
		$p=1;
	}
	if (strlen($newkey1)<8){
		print "New key must be at least 8 characters!<Br>";
		$p=1;
	}
	
	if($p==0){
		#all tests passed; we can change the key
		#first though we must decrypt the SNMP community strings in Devices

		cryptPublic(0,$oldkey);
		cryptPublic(1,$newkey1);

		#register the new key for the session
		$key=$newkey1;
		session_register("key");

		#update the hash
		$hash=md5($newkey1);
		$sql="update EncryptionStatus set secret='$hash'";
		$q=mysql_query($sql);
		
		print "Encryption key successfully changed.  Be sure to restart your Perl scripts!";
	}


function cryptPublic($direction,$key){
	        $mode=MCRYPT_MODE_ECB;
                $cipher = MCRYPT_BLOWFISH;
                $sql="select MAC,public from Devices";
                $q=mysql_query($sql); 
                $r=mysql_fetch_object($q);
                while($r)
                {
                        $mac=$r->MAC;
                        $public=cryptText($direction,$r->public,$cipher,$mode,$key);
                        $sql="update Devices set public='$public' where MAC='$mac'";
	                $q2=mysql_query($sql);
                        #print "$sql\n";
                        $r=mysql_fetch_object($q);
                }
}

