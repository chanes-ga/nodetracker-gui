<?php
include ("services.css");
include("dbConnect.inc");
?>
<body>
<form>
<input type=button value="Back" onClick="history.go(-1)">
</form>
<center>

<?
echo "<div id=header>Port $myport<br>$myservice service<br></div>";
$sql="select nmap.nodeID, description, primaryip from nmap left join Devices using(nodeID) where port=$myport order by primaryip";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);
print "<table border=0>\n<tr><th>Device</th><th>IP/DNS</th></tr>\n";
while ($r){
        $iplist="$r->primaryip";
	
	print "<tr><td valign=top><a href=showDevice.php?nodeID=$r->nodeID>$r->description</a></td><td valign=top>$iplist</td></tr>\n";
	$r=mysql_fetch_object($q);
}
print "</table>";
?>

</body>

