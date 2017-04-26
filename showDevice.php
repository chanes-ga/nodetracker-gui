<head>
<?php
include("dbConnect.inc");
include ("showDevice.css");
include ("crypt.inc");
include ("config.inc");
?>
<script>
	function showBlock(block,v){
		if (v==1){
			block.style.visibility="visible";
		}else{
			block.style.visibility="hidden";
		}

	}

	function verify(myform){
                if(confirm("Are you sure you want to delete this?")==true){
                	myform.submit();
                }

	}

	function verify2(myform){
                if(confirm("Are you sure you want to delete this NODE?")==true){
			myform.updateRecord.value=5;
                	myform.submit();
                }

	}



	function openGraphWindow(shortoid,refid,rrd_dataType,rrd_stacked,rrd_graphType,plotType,ignoredparam){		
		var url="graphMonitor.php?shortoid="+shortoid+"&refid="+refid+"&rrd_dataType="+rrd_dataType+"&rrd_stacked="+rrd_stacked+"&rrd_graphType="+rrd_graphType+"&plotType="+plotType+"&ignoredval="+ignoredparam;
		var w=730;
		var h=650;
		var l = (screen.width - w) / 2;
		var t = (screen.height - h) / 2;
		var features="dependent=yes,resizable=yes,scrollbars=yes,width="+w+",height="+h+",left="+l+",top="+t;
		var con=window.open(url,"Graph",features);
		con.creator=self;
	}

        function openICMPWindow(graphtype,ip){   
                var url="icmpMonitor.php?graphtype="+graphtype+"&ip="+ip
                var w=850;              
                var h=650;
                var l = (screen.width - w) / 2;
                var t = (screen.height - h) / 2;
		var features="dependent=yes,resizable=yes,scrollbars=yes,width="+w+",height="+h+",left="+l+",top="+t;
                var con=window.open(url,"ICMP",features);
                con.creator=self;
        }



</script>
</head>


<body>
<?php
	include ("getInterfaceBlockLayer.inc");
	include ("getOIDInstances.inc");
	include ("getEditBlock.inc");
	include ("getMonitorBlock.inc");
	include ("getICMPBlock.inc");


	#remove whitespace from user input
	$address=trim($address);

	if ((strlen($address)>0)){
		#user specified ip address or DNS
		if($addresstype=="ip"){
			#by ip
			$address=gethostbyname($address);
			$sql="select nodeID from Links left join IP on IP.MAC=Links.MAC where IP='$address'";
		}else{
			#by mac
			$sql="select nodeID from Links where Links.MAC='$address'";

		}
		$q=mysql_query($sql);
		$rows=mysql_num_rows($q);
		if ($rows==1){
			$row=mysql_fetch_row($q);
			$nodeID=$row[0];
		}
		if($rows==0){
			#couldn't find a node - search raw data
			$nodeID=0;
			if($addresstype=="ip"){
				$ip=$address;
				#try to determine MAC
				$sql="select MAC from IP where IP='$address'";
				#print $sql;
				$q=mysql_query($sql);
				$row=mysql_fetch_object($q);
				if(mysql_num_rows($q)>0){
					
					$address=$row->MAC;
				}
			}
			$sql="select Switch,ifnum from Port where MAC='$address'";
			$q=mysql_query($sql);
			$row=mysql_fetch_object($q);
                        if(mysql_num_rows($q)>0){
				$d=getSwitchPortDesc2($row->Switch,$row->ifnum,0);
				$desc=$d[1];
				print "<center><table border=1 cellpadding=4>";
				if($ip){
					print "<tr><th>IP</th><td>$ip</td></tr>";
				}	
				print "<tr><th>MAC</th><td>$address</td></tr></table><br>";
				print "$desc<br>
					<br>Limited information available as this interface is not associated with any known device.";
	


                        }else{
				print "Cannot find further information on IP $ip with MAC $address";
			}
			print "<br><a href=connectIF2Nodes.php?linksingle=1&mac=$address&ip=$ip>Link this IP/MAC to Node</a>";
		}

		
	}else{
		$tmp=split("\|",$nodeID);
		$type=$tmp[1];
		$nodeID=$tmp[0];
	}
	#echo "UR:$updateRecord\n";
	switch ($updateRecord){
		case 1: 
		#editBlockFrm was submitted
                if ($nmap=="on")
                        $nmap=1;
                else
                        $nmap=0;
                if ($icmpscan=="on")
                        $icmpscan=1;
                else
                        $icmpscan=0;

                if ($active=="on")
                        $active=1;                
                else  
                        $active=0;


		if ($cleartext=="on"){
	        	$mode=MCRYPT_MODE_ECB;
			$cipher = MCRYPT_BLOWFISH;
			$public=cryptText(1,$public,$cipher,$mode,$HTTP_SESSION_VARS["key"]);

		}
        
                $sql="update Devices set ownerid=$ownerid,active=$active,icmpscan=$icmpscan,groupid=$groupid,snmpver=$snmpver,IPAutoFill=0,ifDescShortOID=$ifDescShortOID,
			type=$devicetype,public='$public',Description=\"$description\",PrimaryIP='$primaryip',RunNMAP=$nmap 
			where nodeID=$nodeID";
		$savedText="Saved Changes..";
		#$savedText=$sql;
                $q=mysql_query($sql);
		break;

		case 2:
		#monitorBlockFrm was submitted
		$sql="insert into OID_Instances(shortoid,nodeID,status) values($shortoid,$nodeID,1)";
		$q=mysql_query($sql);
		break;

		case 3:
		#delete OID_Instance
		$sql="delete from OID_Instances where refid=$refid";
		$q=mysql_query($sql);
		break;

		case 4:
		#unlink mac
		print "Unlinking $mac<br>";
		$sql="delete from Links where MAC='$mac'";
		$q=mysql_query($sql);
		break;

		case 5:
		#delete node;
		print "Deleting this node $nodeID<br>";
		$sql="delete from Devices where nodeID=$nodeID";
		$q=mysql_query($sql);

		$sql="delete from Links where nodeID=$nodeID";
		$q=mysql_query($sql);

		$sql="delete from nmap where nodeID=$nodeID";
		$q=mysql_query($sql);

		$sql="delete from OID_Instances where nodeID=$nodeID";
		$q=mysql_query($sql);
		exit();
		
		break;	
        }

	$sql="select ownerid,active,icmpscan,groupid,snmpver,ifDescShortOID, Devices.Description as Device, OSGuess, tcpsequence, 
		rating,ratingcomment,RunNMAP,PrimaryIP,public,lastnmap,type 
		from Devices where Devices.nodeID=$nodeID";

	#echo $sql;

	$q=mysql_query($sql);
	if (mysql_num_rows($q)>0){
		$r=mysql_fetch_object($q);
		$rating=$r->rating;
		$primaryip=$r->PrimaryIP;
		
                if ($rating==0){
                        #for whatever reason, a numerical rating was unable to be found
                        $rating="";
                }
		if ($MAC!=$r->PrimaryIP){
			#attempt to get vendor code
			$tmp=split("\.",$MAC);
			$vendorcode=strtoupper($tmp[0].$tmp[1].$tmp[2]);
			$sql="select vendor from EthernetCodes where code='$vendorcode'";
			$q2=mysql_query($sql);
			$r2=mysql_fetch_object($q2);
			if ($r2->vendor){
				$vendor="<br>$r2->vendor NIC";
			}
			$macDisplayValue="$MAC $vendor";
		}else{
			$macDisplayValue="Unknown";
		}
		echo "<form>\n\t<input type=button value=\"Back\" onClick=\"history.go(-1)\">\n";
		if ($HTTP_SESSION_VARS["keyvalid"]>0){
			print "\t<input type=button name=editDevice value=\"  Edit Device  \" onClick=\"showBlock(document.all.editBlock,1);\">\n";
		}
		print "\t<input type=button name=addMonitor value=\" Add SNMP Monitor \" onClick=\"showBlock(document.all.monitorBlock,1);\">\n$savedText\n</form>\n";
		
		echo "<h2>$r->Device</h2>";
                echo "<table border=0 width=350>
			<tr><th align=left>OS Guess</th><td>$r->OSGuess</td></tr>
			<th align=left>TCP Sequencing</th><td>$r->tcpsequence</td></tr>
			<th align=left>Security Rating/Comment</th><td>$rating / $r->ratingcomment</td></tr>
			</table><br>";

		
		
		
		$editBlock=getEditBlock($nodeID,$r->Device,$r->RunNMAP,$r->public,$r->PrimaryIP,$r->type,$r->ifDescShortOID,$r->snmpver,$r->groupid,$r->icmpscan,$r->active,$r->ownerid);


		#show port status on this machine
		$sql="select * from nmap where nodeID=$nodeID order by Port";
		$q=mysql_query($sql);
		if (mysql_num_rows($q)>0){
			echo "<div id=porttable
		            style=\"position: absolute;
	                        top: 300; left: 370;
        	                width: 360;
	                        \"
				>
				<table class=tableStyle border=0><tr>
				<td align=center colspan=4>Last updated <font color=blue>".strftime("%D %T",$r->lastnmap).
				"</font></td></tr>
				<tr><th>Port</th><th>Service</th><th>State</th></tr>";
			for ($i=0;$i<mysql_num_rows($q);$i++){
				$r=mysql_fetch_object($q);
				echo "<tr><td>$r->Port</td><td>$r->Service</td><td>$r->State</td></tr>\n";
			}	
			echo "</table></div>\n";
		}else{
			echo "<br>";
		}

		$excludeSQL="";
		echo getOIDInstances($nodeID,&$excludeSQL);
		echo getMonitorBlock($nodeID,$excludeSQL);
		echo getInterfaceBlocks($db,$nodeID,$r->type);
		echo $editBlock;

		echo getICMPBlock($primaryip);

			
	}else{
		#layer 2 devices not plugged in to any switch
		#echo "$address - no such node.  Please try again.<br>";
		
	}




?>
