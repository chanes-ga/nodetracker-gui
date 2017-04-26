<?php
include ("services.css");
include("dbConnect.inc");
if ($MAC&&$time){
	$sql="delete from nmap_changelog where MAC='$MAC' and timestamp=$time";
	$q=mysql_query($sql);
}
if($ackall==1){
	$sql="delete from nmap_changelog";
	$q=mysql_query($sql); 
} 
?> 
<body> <center>

<? $sql="select timestamp,changedescription, nmap_changelog.MAC,description
	from nmap_changelog left join Devices using(MAC) order by description,timestamp";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);
print "<form><input type=submit value=\"Acknowledge ALL\"><input type=hidden name=ackall value=1></form>";
print "<table border=0 width=450><tr><th>Device</th><th>Alert Time</th><th>Change Description</th></tr>";
while ($r){
	print "<tr><td><a href=showDevice.php?MAC=$r->MAC>$r->description</a></td><td>". strftime("%D 
%T",$r->timestamp)."</td><td>
		$r->changedescription</td><td>
		<a href=servicechanges.php?MAC=$r->MAC&time=$r->timestamp&time2=$r->time2>Acknowledge</a></td></tr>";
	$r=mysql_fetch_object($q);
}
print "</table>";
?>

</body>

