<head>
<META HTTP-EQUIV="Refresh" CONTENT="600">
</head>
<?php

Function getIF($mac){
	if($mac==""){
		return "";
	}
	$sql="select description from IFDescriptions where physAddress='$mac'";
	#echo $sql;
	$q=mysql_query($sql);
	if(mysql_num_rows($q)==0)
		return "";
	$row=mysql_fetch_object($q);
	
	return " ".$row->description;	
}
include ("showGraph.js");
include ("dbConnect.inc");
include ("currentUsage.inc");
$inrefid=getRefID($nodeID,21);
$outrefid=getRefID($nodeID,22);
makeUsageCharts($db,$ip,$inrefid,$outrefid);
$sql="select description as name from Devices where PrimaryIP='$ip'";
$q=mysql_query($sql);
$row=mysql_fetch_object($q);
$name=$row->name;

echo "<h2>$name</h2><table border=0 cellspacing=5>
	<tr><th colspan=2>Port</th><th align=left>Network Node</th><th></th>
	<th align=left>MAC</th>
	<th></th>
	<th align=left>IP Addresses</th><th></th><th>Port Usage</th></tr>";

$sql="select Devices.nodeID,IFDescriptions.Description as ifdesc,speed,Port.MAC as mac, Devices.Description as device,
	Port.ifnum as ifnum
	from Port 
	left join Links using(MAC)
	left join Devices on Devices.nodeID=Links.nodeID
	inner join IFDescriptions on Port.Switch=IFDescriptions.IP and Port.ifnum=IFDescriptions.ifnum
	where Port.switch='$ip' and opStatus=1 and uplink=0

	union

	select 0,IFDescriptions.Description as ifdesc,speed,'' as mac, Crossovers.detail as device, Crossovers.ifnum as ifnum 
	from Crossovers 
	inner join IFDescriptions on Crossovers.Switch=IFDescriptions.IP and Crossovers.ifnum=IFDescriptions.ifnum 
	where Crossovers.Switch='$ip' and opStatus=1

	order by ifnum";



echo $sql;
$q=mysql_query($sql);
for ($i=0;$i<mysql_num_rows($q);$i++){
	$row=mysql_fetch_object($q);
	if ($row->mac!=""){
		$sql2="select count(*) from IP where MAC='$row->mac'";
		$q2=mysql_query($sql2);
		$ipCount=mysql_fetch_row($q2);
	}else{
		$ipCount="";
	}
	$speed=$row->speed/4;
	$mac=$row->mac;
	$ifnum=$row->ifnum;
	$nodeID=$row->nodeID;
	$ifdesc="$row->ifdesc";

	if($row->device==""){
		$device="Unknown";
	}else{
		$device=$row->device;
		$device=$device.getIF($mac);
	}

	if (strlen($row->device)>0){
		$ifdesc=$ifdesc."<br>".$device;
	}
	$ifdesc=urlencode($ifdesc);
	$anchorstart="<a href=javascript:showGraph($ifnum,$inrefid,$outrefid,\"$ifdesc\",$row->speed,'');>";
	$rrdbar="$anchorstart<img border=0 src=\"/$db/data/tmp/$ip.$ifnum.png\"></a>";

	echo "<tr><td><b>$row->ifdesc</td><td width=10></td>
		<td><a href=showDevice.php?nodeID=$nodeID>$device</a></td><td width=10></td>
		<td>$mac</td><td width=10></td>
		<td>$ipCount[0]</td><td></td><td>$rrdbar</td>
		</tr>";
}
?>




</table>
