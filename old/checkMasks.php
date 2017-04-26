<?
include("dbConnect.inc");
include("ipconvert.inc");
include("checkMasks.css");

$sql="select distinct mask,network from RouterIPs where ip>1 order by network";
$q=mysql_query($sql);

$r=mysql_fetch_object($q);

$curNet=$r->network;
$prevNet="";
$problemCount=0;
while($r)
{	
	$curNet=$r->network;
	#print "$curNet<br>";
	if($prevNet==$curNet){
		ShowProblem($curNet);
		$problemCount=$problemCount+1;
	}

	$prevNet=$curNet;
	$r=mysql_fetch_object($q);
}
if ($problemCount>0){
	print "<font color=red>Inconsistent subnet masks found on $problemCount networks!</font>";
}else{
	print "No problems found.  Subnet masks are consistent across all routers.";
}
?>

<?
Function ShowProblem($network){
	$sql="select RouterIPs.ip,network,mask,address,Devices.MAC, Devices.description from RouterIPs
		left join Devices on Devices.PrimaryIP=RouterIPs.ip  where network=$network order by ip";
	#print $sql;
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	
	print "<table width=500 border=0><th colspan=5>".toIP($network)."</th></tr>
		<tr><th align=left>Router Name</th><th align=left>Router MAC</th>
	<th align=left>Router SNMP IP</th>
	<th align=left>Conflicting IP</th><th align=left>Conflicting Mask</th></tr>";
	while($r){
		print "<tr><td>$r->description</td><td>$r->MAC</td>
			<td>$r->ip</td><td>".toIP($r->address)."</td><td>".toIP($r->mask)."</td></tr>";
		$r=mysql_fetch_object($q);
	}
	print "</table><br>";
}
?>
