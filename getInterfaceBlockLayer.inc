<?
Function getInterfaceBlocks($db,$nodeID,$type)
{
	include ("showGraph.js");
	$html="";
	print "node:$nodeID";
	$sql="select Links.MAC, IFDescriptions.ifnum, IFDescriptions.description as description, 
		Port.Switch as switch, 
		Port.ifnum as switchIF
		from Links 
		left join Port on Port.MAC=Links.MAC 
		left join IFDescriptions on IFDescriptions.physAddress=Links.MAC where nodeID=$nodeID order by description";

	#grab all the interfaces for this node...


        $q=mysql_query($sql);
	#echo $sql;
	
        if (mysql_num_rows($q)>0){
                $r=mysql_fetch_object($q);
                while($r){
			$html=$html.getInterfaceBlock($db,$nodeID, $r->ifnum,$r->description,$r->MAC, $r->switch, $r->switchIF,$type);
			$html=$html."<br>";
                        $r=mysql_fetch_object($q);
                }
         
        }

	return $html;
}
Function getInterfaceBlock($db,$nodeID,$ifnum,$description,$mac,$switch, $switchIF,$type)
{
	$html= "\n<div id=interfaceBlock$ifnum
                	style=\"position: relative;
                        top: 10; left: 10;
                        width: 300; padding-left:8; padding-top: 3; padding-bottom:4;
                        background-color: #BBBBBB;
                        overflow:hidden;
                        \">";

	if ($switch!=""){
		$a=getSwitchPortDesc2($switch,$switchIF,1);
		$spdesc=$a[1];
		$speed=$a[0]/4;
	        $ifdesc=urlencode($spdesc);
		$sql="select nodeID from Devices where PrimaryIP='$switch'";
		#echo $sql."\n";
		$q=mysql_query($sql);
		$r=mysql_fetch_object($q);
		$switchNode=$r->nodeID;
		$inrefid=getRefID($switchNode,21);
		$outrefid=getRefID($switchNode,22);

	        $anchorstart="<a href=javascript:showGraph($switchIF,$inrefid,$outrefid,\"$ifdesc\",$speed,'');>";
		$image="/$db/data/tmp/$switch.$switchIF.png";
		$fullpath="/var/www/html/$image";
		if (file_exists($fullpath)){
		        $rrdbar="$anchorstart<img border=0 src=\"$image\"></a>";
		}
	}

	#$sql="select * from IFDescriptions left join Port on Port.MAC=IFDescriptions.physAddress where nodeID=$nodeID";
	$html=$html."
		<table width=100% class=tableStyle2><tr>
		<td class=tableStyle2>Interface $description</td><td class=tableStyle2 width=50>$rrdbar</td>
		<td align=right class=tableStyle2>
		<form>
		<input type=submit value='Unlink'>
		<input type=hidden name=updateRecord value=4>
		<input type=hidden name=mac value='$mac'>
		<input type=hidden name=nodeID value=$nodeID>
		</form>
		</td>
		</table>
		<table class=tableStyle>
		<tr><th>MAC:</th><td>$mac</td></tr>
		<tr><th>Location:</th><td>$spdesc</td></tr>
		</table><br>";


	$sql="select IP,DNS from IP where IP.MAC='$mac'";
	$html=$html.getIPsCase1($sql);
        $html=$html."</div>";
	return $html;        
}

Function getIPsCase1($sql){
        #$sql="select IP,DNS from IP where IP.MAC='$mac'";  
        $q=mysql_query($sql);  
        $rows=mysql_num_rows($q);
	$html="";
        if ($rows>0){
                $html=$html."<table class=tableStyle border=0 width=200><tr><th>IP</th><th>DNS</th></tr>";
                
                for ($i=0;$i<$rows;$i++){
                        $row=mysql_fetch_row($q);
                        $html=$html."<tr><td>$row[0]</td><td width=150>$row[1]</td></tr>";
                } 
                $html=$html."</table>";
        }
	return $html;
}
Function getSwitchPortDesc($switch, $switchIF)
{
	if (strlen($switch)==0) 
		return "";	
	
	$sql="select speed,description from IFDescriptions where IP='$switch' and ifnum=$switchIF";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	$desc="$r->description on $switch";
	$speed=$r->speed;
	$a=array($speed,$desc);
	return $a;

}

Function getSwitchPortDesc2($switch,$switchIF,$format)
{
	$sql="select Devices.description,Devices.nodeID,Devices.PrimaryIP,IFDescriptions.ifnum, 
		IFDescriptions.description as ifdesc,speed,                
        	opStatus,RouterIFs.description as extended,                
         	Port.MAC as mac
                from IFDescriptions
                left join RouterIFs on IFDescriptions.ip=RouterIFs.ip and IFDescriptions.ifnum=RouterIFs.ifnum
                left join Devices on Devices.PrimaryIP='$switch'
                left join Port on Port.Switch=IFDescriptions.IP and Port.ifnum=IFDescriptions.ifnum
                where IFDescriptions.ip='$switch' and IFDescriptions.ifnum=$switchIF";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	$speed=$r->speed;	

	if ($format==0){
	$desc="<table border=0><tr><th>Location</th></tr>
		<tr><th>
		<table border=1 cellpadding=4><tr><th>Switch</th><th>Port</th><th>Description</th></tr>
		<tr><td><a href=showPorts2.php?nodeID=$r->nodeID&ip=$r->PrimaryIP&markif=$switchIF>$r->description</a></td>
			<td>$r->ifdesc</td><td>$r->extended</td></tr></table>
		</th></tr></table>";
	}else{
		if($r->extended){
			$extra="($r->extended)";
		}else{
			$extra="";
		}
		$desc="<a href=showPorts2.php?nodeID=$r->nodeID&ip=$r->PrimaryIP&markif=$switchIF>
			$r->ifdesc $extra on $r->description</a>\n";
	}

	$a=array($speed,$desc);
	return $a;
}
?>
