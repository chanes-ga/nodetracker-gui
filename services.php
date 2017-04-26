<?php
include ("services.css");
include("dbConnect.inc");
?>
<body>
<center>

<?
$sql="select count(*) as c, port,service from nmap group by Port,service order by Port";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);
print "<table border=0><tr><th>Port</th><th>Service</th><th>Device Count</th></tr>";
while ($r){
	print "<tr><td>$r->port</td><td>$r->service</td><td align=right>$r->c</td><td>
		<a href=serviceDetail.php?myport=$r->port&myservice=$r->service>Details</a></td></tr>";
	$r=mysql_fetch_object($q);
}
print "</table>";
?>

</body>

