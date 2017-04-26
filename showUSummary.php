<html>
<head>
<META HTTP-EQUIV="Refresh" CONTENT="600">
<?php
include("showUSummary.css");
include ("graphIF.inc");
include ("dbConnect.inc");
?>
</head>
<body>
<center>

<?php

#get refids for in and out octets
$sql="select OID_Instances.MAC,shortoid,refid from OID_Instances left join Devices using(nodeID) 
	where Devices.PrimaryIP='$ip' and shortoid in($inOID,$outOID) order by shortoid";

print $sql;
$q=mysql_query($sql);
$row=mysql_fetch_object($q);
$inrefid=$row->refid;
$row=mysql_fetch_object($q);
$outrefid=$row->refid;

switch ($type){
	case 2:
		$sql="select RouterIFs.description as extendedDescription,
			IFDescriptions.Description as ifdesc,speed,Port.MAC as mac, Devices.Description as 
			device, Port.ifnum as ifnum from Port 
			left join Devices using(MAC) 
			left join RouterIFs on RouterIFs.IP=Port.switch and RouterIFs.ifnum=Port.ifnum 
			inner join IFDescriptions on Port.Switch=IFDescriptions.IP and Port.ifnum=IFDescriptions.ifnum 
			where Port.switch='$ip' and IFDescriptions.opStatus=1 and speed>0 

			union

		        select '',IFDescriptions.Description as ifdesc,speed,'' as mac, Crossovers.detail as device, 
				Crossovers.ifnum as ifnum
			        from Crossovers
			        inner join IFDescriptions on Crossovers.Switch=IFDescriptions.IP and Crossovers.ifnum=IFDescriptions.ifnum
			        where Crossovers.Switch='$ip' and opStatus=1
			order by ifnum"; 
		
		$sql2="select RouterIFs.description as extendedDescription, 
			IFDescriptions.Description as ifdesc,speed,
			IFDescriptions.ifnum,opStatus from IFDescriptions 
			left join RouterIFs on RouterIFs.IP=IFDescriptions.IP and 
			RouterIFs.ifnum=IFDescriptions.ifnum where IFDescriptions.IP='$ip'";

		break;
	case 3:
        	$sql="select IFDescriptions.Description as ifdesc, IFDescriptions.ifnum, 
			RouterIFs.description as extendedDescription,IFDescriptions.speed
                	from IFDescriptions 
			left join RouterIFs on IFDescriptions.ip=RouterIFs.ip and IFDescriptions.ifnum=RouterIFs.ifnum
			where IFDescriptions.ip='$ip' and opStatus=1 order by IFDescriptions.ifnum";
		break;
}
#print $sql;
$q=mysql_query($sql);
for ($i=0;$i<mysql_num_rows($q);$i++){
        $row=mysql_fetch_object($q);
        $ifnum=$row->ifnum;
	$extendedDescription="";

        if (strlen($row->extendedDescription)>0){
        	$extendedDescription=" (".$row->extendedDescription.")";
        }



	switch ($type){
		case 2:	
			if (strlen($row->device)>0){
				$extendedDescription=$extendedDescription."<br>".$row->device;
			}
			$desc="$row->ifdesc $extendedDescription";
			break;
		case 3:
			#if (strlen($row->extendedDescription)>0){
			#	$extendedDescription="<br>".$row->extendedDescription;
			#}
			$desc="$row->ifdesc $extendedDescription";
			break;
	}
	
	graphIF($db,$ifnum,0,$inrefid,$outrefid,"250","70",$desc,1,$row->speed,"sum");
}

?>

</body></html>
