<?php
##########################################################################################################################################
# Program: tcGet3.php
# Author:  Christopher Hanes
# Description: Generates rc.tc.dynamic for HTB QOS Routers
# Revision: 1.2.1
# Changelog:
# 06/07/05:v1.0.0
# 06/15/05:v1.0.1 added support for specifying remote or local ports in filters
# 07/28/05:v1.1.0 iptables configs modified to employ user-defined chains for improved scalability and performance
# 07/29/05:v1.2.0 default class specified through web interface is now inserted at front of chain instead of appended to end
# 08/01/05:v1.2.1 minor bug fixes
##########################################################################################################################################

include "../ipconvert.inc";
include "dbConnect2.inc";

function buildCMDs($direction,$if,$speed,$pid,$classid,$ipblock,$mask,$subClassCount)
{
	#print "buildCMDS: $ipblock $mask\n";
	$tc="tc";
        $class=  "$tc class add dev $if parent 1:1 classid ".getClassID($classid,0)." htb rate $speed ceil $speed\n";
	$mychain="chain$classid$if";
	$filter="iptables -t mangle -X $mychain\niptables -t mangle -N $mychain\n";

	if($subClassCount==0){		
		$filter=$filter.getIPTableCMD($direction,$if,$ipblock,$mask,"ip","",$classid,0,1,1,"",0);
	}else{
		$filter=$filter.getIPTableCMD($direction,$if,$ipblock,$mask,"ip","",$classid,0,1,1,"",0)."\n";
		
		#need to add leave subclasses and attach filters to these
		$filter=$filter.getSubClasses($direction,$pid,$classid,$if,$speed,$ipblock,$mask);
	}
        $cmd=$class.$filter;
        return $cmd;
}

function getSubClasses($direction,$pid,$parentclassid,$if,$speed,$ipblock,$mask)
{
	#print "getSubClasses: $ipblock $mask\n";
	$tc="tc";
	$class="";
	$sql="select isdefault,ClassID,BWPercentage,BWCeiling,BWPercentageU,BWCeilingU from CanopyQOSClasses 
		where pid=$pid order by ClassID";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	while($r){

		if ($direction==0){
			#download
			$subSpeed=sprintf("%d",($speed*$r->BWPercentage)/100);
			$subCeiling=sprintf("%d",($speed*$r->BWCeiling)/100);
		}else{
			#upload
			$subSpeed=sprintf("%d",($speed*$r->BWPercentageU)/100);
			$subCeiling=sprintf("%d",($speed*$r->BWCeilingU)/100);
		}
		
		if($subSpeed==0){
			$subSpeed=1;
		}
		if($subCeiling==0){
			$subCeiling=1;
		}

		$subSpeed=$subSpeed."kbps";
		$subCeiling=$subCeiling."kbps";
		$class=$class."tc class add dev $if parent ".getClassID($parentclassid,0)." classid ".getClassID($parentclassid,$r->ClassID)." htb rate $subSpeed ceil $subCeiling\n";
		$class=$class.$filter=getSubClassFilters($direction,$if,$pid,$parentclassid,$r->ClassID,$ipblock,$mask,$r->isdefault);
		$r=mysql_fetch_object($q);
	}
	return $class;
}
function getClassID($parent,$child)
{
	$id="1:".($parent+$child);
	return $id;
}
function getSubClassFilters($direction,$if,$pid,$parentclassid,$subclassid,$ipblock,$mask,$isdefault)
{
	#print "getSubClassFilters: $ipblock $mask\n";
	$sql="select * from CanopyQOSFilters where pid=$pid and classID=$subclassid order by id";
	$q=mysql_query($sql);
        $r=mysql_fetch_object($q);
	$filter="";
        while($r){
		$protocol=$r->protocol;
		$portlist=$r->PortList;
		$pdirection=$r->direction;
		$layer7=$r->layer7;
		#print "Portlist $portlist\n";
		
$filter=$filter.getIPTableCMD($direction,$if,$ipblock,$mask,$protocol,$portlist,$parentclassid,$subclassid,$pdirection,0,$layer7,$isdefault);
                $r=mysql_fetch_object($q);
        }

	return $filter."\n";
	
}
function getIPTableCMD($direction,$if, $ipblock,$mask,$protocol,$portlist,$parentid,$childid,$pdirection,$isbaseclass,$layer7,$isdefault){
	$cbqclass=getClassID($parentid,$childid);
	$mychain="chain$parentid$if";
	$ipinfo=$ipblock."/".$mask;
        if ($direction==0){
        	#download so
                $ipinfo=" -d ".$ipinfo;
		if ($pdirection==1){
			#throttle remote port
	                $portdirection=" --sport";
		}else{
			#throttle local port
	                $portdirection=" --dport";
		}
        }else{
        	#upload so
                $ipinfo=" -s ".$ipinfo;
		if($pdirection==1){
			#throttle remote port
	                $portdirection=" --dport";
		}else{
			#throttle local port
	                $portdirection=" --sport";
		}
        }

        if($portlist<>''){
        	$portlist=" -m multiport $portdirection $portlist  ";
        }else{
        	$portlist="";
		
        }

	if($layer7<>''){
		$portlist="";
		$layer7=" -m layer7 --l7proto $layer7";
		$protocol="ip";
	}

	if ($isdefault==0){
		$insertpos=" -A ";
	}else{
		$insertpos=" -I ";
	}
	if ($isbaseclass==0){
		$cmd="iptables -t mangle $insertpos $mychain -p $protocol $portlist $layer7 -j CLASSIFY --set-class $cbqclass\n";
	}else{
		$cmd="iptables -t mangle $insertpos POSTROUTING -o $if $ipinfo -p ip -j $mychain\n";
	}
	return $cmd;
}
function getSubClassCount($pid)
{
	$sql="select count(*) as c from CanopyQOSClasses where pid=$pid";
	#print $sql;
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	return $r->c;
}


$sql="select * from CanopyCustomers 
	left join CanopyQOSRouters on CanopyQOSRouters.ip=CanopyCustomers.linuxgw 
	where linuxgw='$linuxgw' 
	order by classID";

#print $sql;
$q=mysql_query($sql);
$r=mysql_fetch_object($q);

while($r)
{
	$pid=$r->id;
	$ulIf=$r->upIF;
	$dlIf=$r->downIF;
	$classid=$r->classID;
	$subClassCount=getSubClassCount($pid);
	$ipblock=gmp_init($r->ipblock);
	$ipblockhex=gmp_strval($ipblock,16);
	$mask=gmp_init($r->mask);
	$maskhex=gmp_strval($mask,16);
	$dlspeed=($r->dlspeed/8)."kbps";
	$ulspeed=($r->ulspeed/8)."kbps";

	$ipblock=toIP($r->ipblock);
	$mask=getNetBits($r->mask);
	$newclassid=getClassID($classid,0);

	print "# Start ClassID $newclassid\n# QOS Rules for Customer \"$r->customer\"\n";
	$cmd=buildCMDs(0,$dlIf,$dlspeed,$pid,$classid,$ipblock,$mask,$subClassCount);
	print "# Download\n$cmd";
	$cmd=buildCMDs(1,$ulIf,$ulspeed,$pid,$classid,$ipblock,$mask,$subClassCount);
	print "# Upload\n$cmd";
	print "# End ClassID $newclassid\n";
	print "\n\n";

	$r=mysql_fetch_object($q);

}


?>
