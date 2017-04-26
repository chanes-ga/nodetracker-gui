<?
function pushChanges($linuxgw,$parentclassid,$classid,$action){
	$fillfactor=10;
        $parentclassid=$parentclassid*$fillfactor;
        $classid=$parentclassid+$classid;
        $url="https://$linuxgw/tcModify.php?parentclassid=$parentclassid&classid=$classid&action=$action";
	$cmd="/usr/bin/wget \"$url\"";
	#print $cmd;
	system($cmd);
        #print "<br><a href=$url>$url</a><br>";
}
?>

