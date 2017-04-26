<style type="text/css">
.blockDetail{	position: absolute; 
		top: 0; left: 340; 
		width: 370; 
		background-color: #dddddd; 
		border: 2 2 2 2;
		border-style: solid;
		visibility: hidden
	    }
</style>
<?
Function getBlockDetailLayer($network,$subnetsize,$j,$bgcolor,$usage,$mergeStr,$notes){
	$ipallocation=Array();
	$start=0;
	$end=$subnetsize;
	$availableColor="#0000FFF";
	$usedColor="#F09030";
	$table="document.all.table$j";
	if ($notes){
		$notesHtml="<PRE>NOTES\n\n$notes</PRE>";
	}

	if ($subnetsize>1){		
		#we need to indicate network and broadcast addresses
		$broadcastCell=$subnetsize-1;
		$ipallocation[0]="Network Number";
		$ipallocation[$end-1]="Broadcast Address";
		if ($usage==0){
                	$subnetStr="<th>
				<form name=subnetForm$j>
                       		<input type=button value=Subnet onClick=\"openBlockAllocate($j,$network,$subnetsize)\">
				</th></form>";
	        }
	}

        if ($usage==0){
		#then potentially collasable into previous subnet
        	$header="<center>
                	<table border=0><tr>
			$subnetStr
                        $mergeStr
			</tr>
                        </table>
                        </center>";
        }

	#everything in IP
	#composed of 1) ARP table gathered IPs and 2) Router interface gathered IPs which may not have MACs
        $sql="select Devices.nodeID,IP.address,IP.ip,Devices.description as device,IP.mac,IFDescriptions.description as interface 
		from IP 
		left join Links on Links.MAC=IP.MAC 
		left join Devices on Devices.nodeID=Links.nodeID
		left join IFDescriptions on IFDescriptions.physAddress=IP.MAC 
                where sourceType=0 and 
		IP.address>=$network and IP.address<($network+$subnetsize) 
		UNION
	      select Devices.nodeID,IP.address,IP.ip,Devices.description as device,IP.mac,IFDescriptions.description as 
		interface
		from IP 
		left join Devices on Devices.PrimaryIP=IP.sourceIP
		left join IFDescriptions on (IFDescriptions.ifnum=IP.routerif and IFDescriptions.ip=IP.sourceIP) 
		where sourceType=1 and
		IP.address>=$network and IP.address<($network+$subnetsize) 
		order by address";


	#echo $sql;
        $q3=mysql_query($sql);
        for ($k=0; $k<mysql_num_rows($q3); $k++){
               	$r3=mysql_fetch_object($q3);
		$row=gmp_intval(gmp_sub($r3->address,$network));
		if ($r3->device==""){
			$device="Unknown";
			$extra="&address=".toIP($r3->address)."&addresstype=ip";
		}else{
			$device=$r3->device." ".$r3->interface;
			$extra="";
		}
		
		$label="<a href=showDevice.php?nodeID=".$r3->nodeID."$extra>".$device."</a>";
		
		$ipallocation[$row]=$label;
		#print "$row\t$label\t$ipallocation[$row]\n";
        }

	#create the table
	$bc="#000000";	
	$html=$html."\n<div id=block$j class=blockDetail>
		$header<table border=0><tr><th>
		<table border=0 id=table$j>";
	$baseval=gmp_init($network);
	for ($k=$start;$k<$end;$k++){
		if ($ipallocation[$k]){
			$cellcolor=$usedColor;
		}else{
			$cellcolor=$availableColor;
		}
		$gmpk=gmp_init($k);
		$tmp=gmp_add($baseval,$gmpk);
		$html=$html."<tr><td bgcolor=$cellcolor width=10></td>
		<td class=ipblock>".toIP($tmp)."</td><td class=ipblock>".$ipallocation[$k]."</td></tr>";
	}
	$html=$html."\n\t</table>\n</th><td valign=top>$notesHtml
	</td></tr></table></div>\n";
	return $html;

}

?>
