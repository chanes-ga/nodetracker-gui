<?php
##########################################################################################################################################
# Program: tcGet2.php
# Author:  Christopher Hanes
# Description: Generates rc.tc.dynamic for HTB QOS Routers
# Revision: 1.0.0
# Changelog:
# 06/07/05:v1.0.0
##########################################################################################################################################

include "../ipconvert.inc";
include "dbConnect2.inc";

function buildCMDs($direction,$if,$speed,$pid,$classid,$ipblock,$mask,$subClassCount)
{
	#print "buildCMDS: $ipblock $mask\n";
	$tc="tc";
        $class=  "$tc class add dev $if parent 1:1 classid ".getClassID($classid,0)." htb rate $speed ceil $speed\n";
	if($subClassCount==0){
		
		$filter=getIPTableCMD($direction,$if,$ipblock,$mask,"ip","",getClassID($classid,0));
	}else{
		#need to add leave subclasses and attach filters to these
		$filter=getSubClasses($direction,$pid,$classid,$if,$speed,$ipblock,$mask);
	}
        $cmd=$class.$filter;
        return $cmd;
}

function getSubClasses($direction,$pid,$parentclassid,$if,$speed,$ipblock,$mask)
{
	#print "getSubClasses: $ipblock $mask\n";
	$tc="tc";
	$class="";
	$sql="select ClassID,BWPercentage,BWCeiling,BWPercentageU,BWCeilingU from CanopyQOSClasses 
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
		$class=$class."$tc class add dev $if parent ".getClassID($parentclassid,0)." classid ".getClassID($parentclassid,$r->ClassID)." htb rate $subSpeed ceil $subCeiling\n";
		$class=$class.$filter=getSubClassFilters($direction,$if,$pid,$parentclassid,$r->ClassID,$ipblock,$mask);
		$r=mysql_fetch_object($q);
	}
	return $class;
}
function getClassID($parent,$child)
{
	$id="1:".($parent+$child);
	return $id;
}
function getSubClassFilters($direction,$if,$pid,$parentclassid,$subclassid,$ipblock,$mask)
{
	#print "getSubClassFilters: $ipblock $mask\n";
	$sql="select * from CanopyQOSFilters where pid=$pid and classID=$subclassid order by id";
	$q=mysql_query($sql);
        $r=mysql_fetch_object($q);
	$filter="";
        while($r){
		$protocol=$r->protocol;
		$portlist=$r->PortList;
		#print "Portlist $portlist\n";
		$filter=$filter.getIPTableCMD($direction,$if,$ipblock,$mask,$protocol,$portlist,getClassID($parentclassid,$subclassid));
                $r=mysql_fetch_object($q);
        }

	return $filter."\n";
	
}
function getIPTableCMD($direction,$if, $ipblock,$mask,$protocol,$portlist,$cbqclass){
	$ipinfo=$ipblock."/".$mask;
        if ($direction==0){
        	#download so
                $ipinfo=" -d ".$ipinfo;
                $portdirection=" --sport";
        }else{
        	#upload so
                $ipinfo=" -s ".$ipinfo;
                $portdirection=" --dport";

        }

        if($portlist<>''){
        	$portlist=" -m multiport $portdirection $portlist  ";
        }else{
        	$portlist="";
        }

	$cmd="iptables -t mangle -A POSTROUTING -o $if $ipinfo -p $protocol $portlist -j CLASSIFY --set-class $cbqclass\n";
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
