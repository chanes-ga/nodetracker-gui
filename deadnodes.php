<?php
include ("services.css");
include("dbConnect.inc");
if ($nodeID){
	$sql="delete from Devices where nodeID=$nodeID";
	$q=mysql_query($sql);
}
if ($sort){
	$orderby="order by $sort";
}else{
	$orderby="order by lastactive,description";
}
?>
<body>
<center>

<?
function getOIDs($nodeID)
{
	$sql="select count(*) as c from OID_Instances where nodeID=$nodeID";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	return $r->c;

}
$time=time()-3600*24*7;
$sql="select * from Devices where lastactive<$time $orderby";
$q=mysql_query($sql);
$r=mysql_fetch_object($q);
print "<table border=0><tr><th><a href=deadnodes.php?sort=description>Device</a></th>
	<th><a href=deadnodes.php?sort=MAC>MAC</a></th><th><a href=deadnodes.php?sort=PrimaryIP>Primary IP</a></th>
	<th><a href=deadnodes.php?sort=lastactive>Last Active</a></th><th>OIDs</th><th></th></tr>";
while ($r){
	$oidCount=getOIDs($r->nodeID);
	print "<tr><td><a href=showDevice.php?nodeID=$r->nodeID>$r->Description</a></td>
	<td>$r->MAC</td><td>$r->PrimaryIP</td><td>".strftime("%D %T",$r->lastactive)."</td>
	<td>$oidCount</td>
	<td><a href=deadnodes.php?nodeID=$r->nodeID>DELETE</a></td></tr>";
	$r=mysql_fetch_object($q);
}
print "</table>";
?>

</body>

