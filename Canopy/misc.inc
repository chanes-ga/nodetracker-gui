<?
function pushChanges($pid,$classid,$action,$linuxgw){
	if(($classid==0)&&($action==3)){
		#we've already deleted record from CanopyCustomers
		$parentclassid=$pid;
	}else{
		$sql="select linuxgw,classID from CanopyCustomers where id=$pid";
		print $sql;
		$q=mysql_query($sql);
	        $r=mysql_fetch_object($q);
	        $linuxgw=$r->linuxgw;
		$parentclassid=$r->classID;
	}

	#$linuxgw="216.230.0.99";

        $classid=$parentclassid+$classid;
        $url="https://$linuxgw/tcModify.php?parentclassid=$parentclassid&classid=$classid&action=$action";
	$cmd="/usr/bin/wget \"$url\"";
	print $cmd;
	#system($cmd);
        #print "<br><a href=$url>$url</a><br>";
}
?>

