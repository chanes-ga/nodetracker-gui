<?php
include "../ipconvert.inc";
include "dbConnect2.inc";

function getClassID($parent,$child)
{
	$fillfactor=10;
	$id=(($parent*$fillfactor)+$child);
	return $id;
}
function getSubClasses($parentclassid)
{
	$sql="select ClassID from CanopyQOSClasses where ParentClassID=$parentclassid order by ClassID";
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

function showChildren($parentclassid,$ulIf,$dlIf)
{
        $sql="select ClassID,description from CanopyQOSClasses where ParentClassID=$parentclassid order by ClassID";
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

#print $sql;
$q=mysql_query($sql);
$r=mysql_fetch_object($q);
$classList="";
while($r)
{
	$ulIF=$r->upIF;
	$dlIF=$r->downIF;
	$classid=$r->classID;
	$classList=$classList.getClassID($classid,0).",";
	
	$subClasses=getSubClasses($classid);
	$ipblock=gmp_init($r->ipblock);
	$ipblockhex=gmp_strval($ipblock,16);
	$mask=gmp_init($r->mask);
	$maskhex=gmp_strval($mask,16);
	$dlspeed=($r->dlspeed/8)."kbps";
	$ulspeed=($r->ulspeed/8)."kbps";

	$ipblock=toIP($r->ipblock);
	$mask=getNetBits($r->mask);

	

	print "# tcGraph Data for Customer \"$r->customer\"\n";
	$prefix="\$rrds{".getClassID($classid,0)."}";
	print $prefix."{name}=\"$r->customer\";\n";
	print $prefix."{upif}='$r->upIF';\n";
	print $prefix."{downif}='$r->downIF';\n";
	if($subClasses<>""){
		print $prefix."{children}=$subClasses;\n\n";
		
		showChildren($classid,$r->upIF,$r->downIF);

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
