<?php
include "../ipconvert.inc";
include "dbConnect2.inc";

function buildCMDs($if,$speed,$classid,$ipblockhex,$maskhex,$offset)
{
	$tc="tc";
        $class=  "$tc class add dev $if parent 1:1 classid 1:$classid htb rate $speed ceil $speed\n";
        $filter= "$tc filter add dev $if pref $classid protocol ip parent 1:0 u32 match u32 0x$ipblockhex 0x$maskhex at $offset flowid 1:$classid\n";
        $cmd=$class.$filter;
        return $cmd;
}




$sql="select * from CanopyCustomers where linuxgw='$linuxgw' order by classID";

#print $sql;
$q=mysql_query($sql);
$r=mysql_fetch_object($q);

while($r)
{
	$classid=$r->classID;
	$ipblock=gmp_init($r->ipblock);
	$ipblockhex=gmp_strval($ipblock,16);
	$mask=gmp_init($r->mask);
	$maskhex=gmp_strval($mask,16);
	$dlspeed=($r->dlspeed/8)."kbps";
	$ulspeed=($r->ulspeed/8)."kbps";



	print "# TC Rules for Customer \"$r->customer\" with classid $classid\n";
	$cmd=buildCMDs($dlIf,$dlspeed,$classid,$ipblockhex,$maskhex,16);
	print "# Download\n$cmd";
	$cmd=buildCMDs($ulIf,$ulspeed,$classid,$ipblockhex,$maskhex,12);
	print "# Upload\n$cmd";
	print "\n\n";

	$r=mysql_fetch_object($q);

}


?>
