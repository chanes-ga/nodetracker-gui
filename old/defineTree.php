<?php

#basic static framework
print "foldersTree = gFld(\"<b>My Network</b>\",'')\n";
print "insDoc(foldersTree, gLnk(0, \"Quick Search\", \"qSearch.html\"))\n";
print "insDoc(foldersTree, gLnk(0, \"Canopy QOS\", \"Canopy/index.php\"))\n";
print "insDoc(foldersTree, gLnk(0, \"Inactive Nodes\", \"deadnodes.php\"))\n";
print "insDoc(foldersTree, gLnk(0, \"Link IFs\", \"connectIF2Nodes.php\"))\n";
print "netservices=insFld(foldersTree,gFld(\"Network Services\",\"\"))\n";
print "insDoc(netservices, gLnk(0, 'Map', 'services.php'))\n";
print "insDoc(netservices, gLnk(0, 'Service Changes', 'servicechanges.php'))\n";
print "ipspace=insFld(foldersTree, gFld('IP Space',''))\n";
print "insDoc(ipspace, gLnk(0, 'Configure Blocks', 'configureIPSpace.php'))\n";
print "insDoc(ipspace, gLnk(0, 'Search Subnet Assignments', 'ipSearch.html'))\n";
print "insDoc(ipspace, gLnk(0, 'Check Subnet Mask Consistency', 'checkMasks.php'))\n";
print "switches=insFld(foldersTree, gFld('Ethernet Switches',''))\n";
if ($HTTP_SESSION_VARS["keyvalid"]>0){
	print "switchConfigs=insFld(switches, gFld('Configure',''))\n";
	print "insDoc(switchConfigs, gLnk(0,'Add Device','addDevice.php?type=2'))\n";
}


print "routers=insFld(foldersTree, gFld('Routers',''))\n";
if ($HTTP_SESSION_VARS["keyvalid"]>0){
	print "routerConfigs=insFld(routers, gFld('Configure',''))\n";
	print "insDoc(routerConfigs, gLnk(0,'Interface Descriptions','configureRouterIF.php'))\n";
	print "insDoc(routerConfigs, gLnk(0,'Add Device','addDevice.php?type=3'))\n";
}
print "misc=insFld(foldersTree,gFld('Miscellaneous',''))\n";
print "insDoc(misc, gLnk(0, 'SNMP OIDs', 'configureOID.php'))\n";
print "insDoc(misc, gLnk(0, 'Encryption', 'cryptScreen.php'))\n";
print "insDoc(misc, gLnk(0, 'Activity Scheduling', 'scheduleScreen.php'))\n";



#get Class C info
$sql="select * from IPBlocks order by network";
$q=mysql_query($sql);

for ($i=0;$i<mysql_num_rows($q);$i++){
	$row=mysql_fetch_object($q);
	$blocksize=computeNetSize($row->mask);
	$offset=0;

	$classC=gmp_init($row->network);
	#$classC=$row->network;
	#print "classC= $classC\n";
	while($offset<$blocksize){
		$used=computeNetAllocation($classC,256);
		#$used=0;
		$netstr= toIP($classC)." &nbsp ($used used)";

	        print "insDoc(ipspace, gLnk(0,'$netstr','showClassC.php?network=".gmp_strval($classC)."&size=$blocksize','',''))\n";

		$classC=gmp_add($classC,256);
		$offset+=256;
	}
		
}



#type 2 = layer 2 device
$sql="select nodeID,MAC,PrimaryIP as ip,description as name from Devices where type=2 order by name";
$q=mysql_query($sql);
for ($i=0;$i<mysql_num_rows($q);$i++){
        $row=mysql_fetch_object($q);
	$ip=$row->ip;
	$mac=$row->MAC;
	$nodeID=$row->nodeID;
	$device=$row->name;	
	print "switch$i=insFld(switches, gFld('$device',''));\n";
	print "insDoc(switch$i, gLnk(0,'Configure','showDevice.php?nodeID=".$nodeID."'))\n";
	print "insDoc(switch$i, gLnk(0,'Interface Descriptions','configureRouterIF.php?hide=1&mode=1&ip=".$ip."'));\n";
	print "insDoc(switch$i, gLnk(0,'Port Assignments','showPorts2.php?nodeID=".$nodeID."&ip=".$ip."'));\n";
	print "insDoc(switch$i, gLnk(0,'Utilization Stats','showUSummary.php?type=2&ip=".$ip."'));\n";
}


#type 3 = layer 3 device
$sql="select nodeID,MAC,PrimaryIP as ip,description as name  from Devices where type=3 order by name";
$q=mysql_query($sql);
for ($i=0;$i<mysql_num_rows($q);$i++){
        $row=mysql_fetch_object($q);
	$ip=$row->ip;
	$mac=$row->MAC;
	$nodeID=$row->nodeID;
	$device=$row->name;
	print "router$i=insFld(routers, gFld('$device',''))\n";
	print "insDoc(router$i, gLnk(0,'Configure','showDevice.php?nodeID=$nodeID'));\n";
	print "insDoc(router$i, gLnk(0,'Interface Descriptions','configureRouterIF.php?hide=1&mode=1&ip=".$ip."'));\n";
	print "insDoc(router$i, gLnk(0,'Utilization Stats','showUSummary.php?type=3&ip=".$ip."'));\n";
} 


?>


