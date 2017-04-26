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
Function getDevice($mac){
        $sql="select Description,Links.nodeID from Links 
		left join Devices using(nodeID)
		where Links.MAC='$mac'";
        #echo $sql;
        $q=mysql_query($sql);
        if(mysql_num_rows($q)==0)
                return "";
        $row=mysql_fetch_object($q);
	
        $tmp="<a href=showDevice.php?nodeID=$row->nodeID>".$row->Description." ".getIF($mac)."</a>";
	return $tmp;
}

#$ip="10.0.2.51";
#$nodeID=520;
include ("showGraph.js");
include ("dbConnect.inc");
include ("currentUsage.inc");
include ("showPorts2.css");
$inrefid=getRefID($nodeID,21);
$outrefid=getRefID($nodeID,22);
makeUsageCharts($db,$ip,$inrefid,$outrefid);
$sql="select description as name from Devices where PrimaryIP='$ip'";
$q=mysql_query($sql);
$row=mysql_fetch_object($q);
$name=$row->name;

echo "<h2>$name</h2><table border=1 cellspacing=3>
	<tr><th colspan=1>Port</th><th>Desc</th><th align=left>Node</th><th></th>
	<th align=left>MAC</th>
	<th></th>
	<th align=left>IP Addresses</th><th></th><th>Port Usage</th><th>Link</th></tr>";

#strange need whitespace filled string in order to get detail selected in the lower select statement for crossovers
$sql="select IFDescriptions.ifnum, IFDescriptions.description as ifdesc,speed, opStatus,RouterIFs.description as extended,
			Port.MAC as mac,'                                                    ' as detail
                        from IFDescriptions
			left join Crossovers on Crossovers.Switch=IFDescriptions.IP and Crossovers.ifnum=IFDescriptions.ifnum 
                        left join RouterIFs on IFDescriptions.ip=RouterIFs.ip and IFDescriptions.ifnum=RouterIFs.ifnum
			left join Port on Port.Switch=IFDescriptions.IP and Port.ifnum=IFDescriptions.ifnum
                        where IFDescriptions.ip='$ip' and Crossovers.Switch is null
	union
	select  Crossovers.ifnum,IFDescriptions.Description as ifdesc,speed, opStatus, RouterIFs.description as extended,
		'N/A' as MAC, Crossovers.detail from 
		Crossovers 
		left join RouterIFs on Crossovers.Switch=RouterIFs.ip and Crossovers.ifnum=RouterIFs.ifnum
		inner join IFDescriptions on Crossovers.Switch=IFDescriptions.IP and Crossovers.ifnum=IFDescriptions.ifnum 
		where IFDescriptions.IP='$ip' order by ifnum";
	

#echo $sql;
$q=mysql_query($sql);
for ($i=0;$i<mysql_num_rows($q);$i++){
	$row=mysql_fetch_object($q);
	$mac=$row->mac;
	$speed=$row->speed/4;
	$ifnum=$row->ifnum;
	$nodeID=$row->nodeID;
	$ifdesc="$row->ifdesc";
	$extendedDesc=$row->extended;
	$link=$row->opStatus;
	$device="";
	$detail=trim($row->detail);
	if ($mac!=""){
		$sql2="select IP from IP where MAC='$row->mac'";
		$q2=mysql_query($sql2);
		$c=mysql_num_rows($q2);
		if($c==1){	
			$row2=mysql_fetch_object($q2);
			$ipCount=$row2->IP;
		}else{
			$ipCount=$c;
		}
		$device=getDevice($mac);
	}else{
		$sql2="select MAC from MACData where Switch='$ip' and ifnum=$ifnum";
		$q2=mysql_query($sql2);
		$row2=mysql_fetch_object($q2);
		while($row2){
			$mac=$row2->MAC."<br>";
			$row2=mysql_fetch_object($q2);
		}
		$ipCount="";
	}
	if(($device=="")&&($row->opStatus==1)){
		if(strlen($detail)==0){
			$device="Unknown";
		}else{
			$device=$detail;
		}

	}

	if (strlen($row->device)>0){
		$ifdesc=$ifdesc."<br>".$device;
	}
	$ifdesc=urlencode($ifdesc);

	$anchorstart="<a href=javascript:showGraph($ifnum,$inrefid,$outrefid,\"$ifdesc\",$row->speed,'');>";
	$rrdbar="$anchorstart<img border=0 src=\"/$db/data/tmp/$ip.$ifnum.png\"></a>";


	if($link==1){
		$link="<font color=green>UP</font>";
	}else{
		$link="<font color=red>DOWN</font>";
		$rrdbar="";
	}
	if ($ifnum==$markif){
		$class="class=highlight";
	}else{
		$class="";
	}

	echo "<tr>
		<td $class><b>$row->ifdesc</td>
		<td $class>$extendedDesc</td>
		<td $class width=150>$device</td><td width=10></td>
		<td $class>$mac</td><td width=10></td>
		<td $class>$ipCount</td><td></td>
		<td $class>$rrdbar</td>
		<td $class>$link</td>
		</tr>\n";
}
?>




</table>
