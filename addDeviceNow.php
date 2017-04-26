<?php
include("dbConnect.inc");
include ("crypt.inc");

if ($cleartext=="on"){
	$mode=MCRYPT_MODE_ECB;
        $cipher = MCRYPT_BLOWFISH;
        $public=cryptText(1,$public,$cipher,$mode,$HTTP_SESSION_VARS["key"]);
}   

$sql="insert into Devices(MAC,PrimaryIP,description,public,type) values('$mac','$primaryip',\"$description\",'$public',$devicetype)";
#print $sql;
$q=mysql_query($sql);
$url="showDevice.php?MAC=$primaryip";
header("Location: $url"); 
?>
