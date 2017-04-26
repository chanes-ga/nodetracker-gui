<pre>
<?
include ("dbConnect.inc");
include ("crypt.inc");

$mode=MCRYPT_MODE_ECB;
$cipher = MCRYPT_BLOWFISH;

$sql="select MAC,public from Devices";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);

while($r)
{
	$mac=$r->MAC;
	$public=cryptText(1,$r->public,$cipher,$mode,$HTTP_SESSION_VARS["key"]);
	$sql="update Devices set public='$public' where MAC='$mac'";
	$q2=mysql_query($sql);
	print $sql."<br>";
	$r=mysql_fetch_object($q);
}


?>
