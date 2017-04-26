<?php
##########################################################################################################################################
# Program: tcPerl.php 
# Author:  Christopher Hanes
# Description: Generates tcGraph.inc data file used by tcGraph.pl on  HTB QOS Routers
# Revision: 1.0.0
# Changelog:
# 06/07/05:v1.0.0                                                               
##########################################################################################################################################

include "../ipconvert.inc";
include "dbConnect2.inc";

function getClassID($parent,$child)
{
	$id=($parent+$child);
	return $id;
}
function getSubClasses($pid,$parentclassid)
{
	$sql="select ClassID from CanopyQOSClasses where pid=$pid order by ClassID";
	$subClasses="";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);
	while($r){
		$subClasses=$subClasses.getClassID($parentclassid,$r->ClassID).",";

		$r=mysql_fetch_object($q);
	}
	if ($subClasses<>""){
		$subClasses="\"".substr("$subClasses", 0, -1)."\"";
	}
	return $subClasses;
}

function showChildren($pid,$parentclassid,$ulIf,$dlIf)
{
        $sql="select ClassID,description from CanopyQOSClasses where pid=$pid order by ClassID";
        $q=mysql_query($sql);
        $r=mysql_fetch_object($q);
        while($r){
                $classid=getClassID($parentclassid,$r->ClassID);
        	$prefix="\$rrds{".$classid."}";
        	print $prefix."{name}=\"$r->description\";\n";
        	print $prefix."{upif}='$ulIf';\n";
        	print $prefix."{downif}='$dlIf';\n";
                print $prefix."{children}=undef;\n\n";
      
                $r=mysql_fetch_object($q);
        }
}



$sql="select * from CanopyCustomers 
	left join CanopyQOSRouters on CanopyQOSRouters.ip=CanopyCustomers.linuxgw 
	where linuxgw='$linuxgw' 
	order by classID";

$q=mysql_query($sql);
$r=mysql_fetch_object($q);
$classList="";
while($r)
{
	$pid=$r->id;
	$ulIF=$r->upIF;
	$dlIF=$r->downIF;
	$classid=$r->classID;
	$classList=$classList.getClassID($classid,0).",";
	
	$subClasses=getSubClasses($pid,$classid);
	$ipblock=gmp_init($r->ipblock);
	$ipblockhex=gmp_strval($ipblock,16);
	$mask=gmp_init($r->mask);
	$maskhex=gmp_strval($mask,16);
	$dlspeed=($r->dlspeed/8)."kbps";
	$ulspeed=($r->ulspeed/8)."kbps";

	$ipblock=toIP($r->ipblock);
	$mask=getNetBits($r->mask);

	

	print "# tcGraph Data for Customer \"$r->customer\"\n";
	print "\$sitename=\"$r->customer\";\n\n";
	$prefix="\$rrds{".getClassID($classid,0)."}";
	print $prefix."{name}=\"$r->customer\";\n";
	print $prefix."{upif}='$r->upIF';\n";
	print $prefix."{downif}='$r->downIF';\n";
	if($subClasses<>""){
		print $prefix."{children}=$subClasses;\n\n";
		
		showChildren($pid,$classid,$r->upIF,$r->downIF);

	}else{
		print $prefix."{children}=undef;\n\n";
	}


	$r=mysql_fetch_object($q);

}

$classList="\"".substr("$classList", 0, -1)."\"";
print "# tcGraph Data for Root Class\n";
$prefix="\$rrds{1}";
print $prefix."{name}='Root Class';\n";
print $prefix."{upif}='$ulIF';\n";
print $prefix."{downif}='$dlIF';\n";
print $prefix."{children}=$classList;\n\n";


?>
