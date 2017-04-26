<?
include("dbConnect.inc");
include("iputils.inc");
switch($updateAction){
	case "1":
		$sql="update IPAllocations set description=\"$description\",notes=\"$notes\",ownerid=$ownerid where network=$mynetwork and mask=$mymask";
		$q=mysql_query($sql);
		break;
	case "2":
		$sql=ereg_replace("\\\\", "", $sql);
		$sqls=split(";",$sql);
		$t=count($sqls);
		for($i=0;$i<$t;$i++){
			#echo $sqls[$i]."\n";
			$q=mysql_query($sqls[$i]);
		}
		break;
	case "3":
		#collapse into larger subnet
		$sqlwhere=" network>=$mynetwork and network<($mynetwork+$mergedSize)";
		$b=gmp_init("4294967295");
		$mask=gmp_strval(gmp_sub($b,($mergedSize-1)));

		$sql="delete from RouterIPs where IP in('0','1') and $sqlwhere";
		#echo "$sql\n";
		$q=mysql_query($sql);

		$sql="delete from IPAllocations where $sqlwhere";
		#echo "$sql\n";
		$q=mysql_query($sql);

		$sql="insert into RouterIPs values(0,$mynetwork,$mask,$mynetwork,0,0)";
		#echo "$sql\n";
		$q=mysql_query($sql);

		$sql="insert into IPAllocations(network,mask,description) values($mynetwork,$mask,\"UnAllocated\")";
		#echo "$sql\n";
		$q=mysql_query($sql);

}
#$netEnd=gmp_strval(gmp_add($network,256));
if (($size>256)||($size=="")){
	#show a maximum of one classC at a time
	$size=256;
}
$netEnd=gmp_strval(gmp_add($network,$size));
showIPBlocks($network,$netEnd);
?>



